<?php

namespace App\Services\Stuffing;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Harmonizes the production orders generated from stuffing weighings with what the
 * dispatch transfers actually weighed.
 *
 * Not every line of the BOM chain is measured. Stuffing weighs the input (the item
 * fed into the stuffer); the IDT transfer to dispatch weighs the packed output. The
 * generator fills everything in between from recipe yields, so the chain's output
 * rarely matches what reached dispatch. This rebuilds a batch's lines from both
 * measured ends, with the difference (the yield variance) placed at one step:
 *
 *  - recipe           every quantity from the recipe, scaled from the stuffing weight (as generated)
 *  - output_at_last   the last step (Packing) produces the dispatched weight; its packaging scales with
 *                     that output; earlier steps stay on recipe yield, so the variance shows on Packing
 *  - output_at_first  worked back from the dispatched weight: every step's output is what the next step
 *                     needed, so the variance shows on the first step (Stuffing), whose measured input
 *                     is kept
 *
 * A batch is batch_no + packed item. Its dispatched weight is shared out over the batch's weighings in
 * proportion to the output each was expected to give. Lines already in ProductionData, or posted on the
 * plan route (published), are never changed.
 */
class StuffingHarmonizer
{
    const MODES = [
        'recipe' => 'Recipe only (as generated)',
        'output_at_last' => 'Measured output, variance on the last step (Packing)',
        'output_at_first' => 'Measured output, variance on the first step (Stuffing)',
    ];

    private ProductionDataWriter $writer;

    public function __construct(ProductionDataWriter $writer)
    {
        $this->writer = $writer;
    }

    public static function defaultMode(): string
    {
        $mode = config('production_orders.harmonize_mode', 'output_at_last');

        return isset(self::MODES[$mode]) ? $mode : 'output_at_last';
    }

    /**
     * One row per batch + packed item with weighings between the dates.
     */
    public function batches(string $from, string $to, ?string $search = null, string $state = 'open'): Collection
    {
        $groups = DB::table('generated_production_orders')
            ->whereBetween('transaction_date', [$from, $to])
            ->whereNotNull('batch_no')->whereNotNull('packed_item')
            ->when($search, fn($q) => $q->where(fn($w) => $w->where('batch_no', 'like', "%{$search}%")->orWhere('packed_item', 'like', "%{$search}%")))
            ->groupBy('batch_no', 'packed_item')
            ->select(
                'batch_no', 'packed_item',
                DB::raw('COUNT(DISTINCT idt_transfer_id) as weighings'),
                DB::raw('MIN(transaction_date) as first_date'),
                DB::raw('MAX(harmonized_at) as harmonized_at'),
                DB::raw('MAX(harmonize_mode) as harmonize_mode'),
                DB::raw('SUM(CASE WHEN production_data_at IS NOT NULL OR bc_staged_at IS NOT NULL OR released_at IS NOT NULL OR published = 1 THEN 1 ELSE 0 END) as locked_lines'),
                DB::raw("COUNT(DISTINCT production_order_no) as orders"),
                DB::raw("COUNT(DISTINCT CASE WHEN line_type = 'output' AND plan_order_no IS NOT NULL THEN production_order_no END) as planned_orders"),
                DB::raw('COUNT(*) as lines')
            )
            ->orderByDesc(DB::raw('MIN(transaction_date)'))
            ->orderBy('batch_no')
            ->limit(500)
            ->get();

        if ($groups->isEmpty()) {
            return $groups;
        }

        $dispatched = $this->dispatchedFor($groups->pluck('batch_no')->unique()->values()->all());

        $rows = $groups->map(function ($g) use ($dispatched) {
            $stats = DB::table('generated_production_orders')
                ->where('batch_no', $g->batch_no)->where('packed_item', $g->packed_item)
                ->selectRaw("SUM(CASE WHEN line_type = 'output' AND item_no = packed_item THEN quantity ELSE 0 END) as packed_out")
                ->first();
            $in = DB::table('generated_production_orders')
                ->where('batch_no', $g->batch_no)->where('packed_item', $g->packed_item)
                ->select('idt_transfer_id', DB::raw('MAX(net_weight) as net_weight'))
                ->groupBy('idt_transfer_id')->get()->sum('net_weight');

            $g->stuffed_kg = (float) $in;
            $g->packed_out_kg = (float) $stats->packed_out;
            $g->dispatched_kg = (float) ($dispatched[$g->batch_no . '|' . $g->packed_item]->kg ?? 0);
            $g->transfers = (int) ($dispatched[$g->batch_no . '|' . $g->packed_item]->transfers ?? 0);
            $g->state = $g->locked_lines >= $g->lines ? 'released' : ($g->harmonized_at ? 'harmonized' : 'pending');

            return $g;
        });

        return $state === 'all' ? $rows->values() : $rows->filter(fn($g) => $state === 'open' ? $g->state !== 'released' : $g->state === $state)->values();
    }

    /**
     * What the dispatch transfers weighed, per batch + product.
     */
    public function dispatchedFor(array $batchNos): array
    {
        $out = [];
        foreach (array_chunk($batchNos, 500) as $chunk) {
            DB::table('idt_transfers')
                ->whereIn('batch_no', $chunk)
                ->where('transfer_from', config('production_orders.harmonize_output_from', '2055'))
                ->groupBy('batch_no', 'product_code')
                ->select('batch_no', 'product_code', DB::raw('SUM(total_weight) as kg'), DB::raw('COUNT(*) as transfers'))
                ->get()
                ->each(function ($r) use (&$out) { $out[$r->batch_no . '|' . $r->product_code] = $r; });
        }

        return $out;
    }

    /**
     * Work out the batch's lines under a mode without saving anything.
     *
     * @return array{lines: Collection, measured_in: float, measured_out: float, expected_out: float, errors: array, mode: string}
     */
    public function plan(string $batchNo, string $packedItem, string $mode, ?float $measuredOut = null): array
    {
        if (!isset(self::MODES[$mode])) {
            throw new \InvalidArgumentException("Unknown harmonize mode {$mode}.");
        }

        $lines = DB::table('generated_production_orders')
            ->where('batch_no', $batchNo)->where('packed_item', $packedItem)
            ->orderBy('idt_transfer_id')->orderBy('step')->orderBy('line_no')
            ->get();

        if ($measuredOut === null) {
            $measuredOut = (float) ($this->dispatchedFor([$batchNo])[$batchNo . '|' . $packedItem]->kg ?? 0);
        }

        $recipeRows = $this->recipeRows($lines->pluck('recipe')->filter()->unique()->values()->all());

        $errors = [];
        $chains = [];
        foreach ($lines->groupBy('idt_transfer_id') as $transferId => $weighing) {
            try {
                $chains[$transferId] = $this->chain($weighing, $recipeRows);
            } catch (\RuntimeException $e) {
                $errors[] = "Weighing {$transferId}: " . $e->getMessage();
            }
        }

        $expectedTotal = array_sum(array_map(fn($c) => $c['expected_out'], $chains));
        $measuredIn = (float) $lines->groupBy('idt_transfer_id')->map(fn($w) => (float) $w->first()->net_weight)->sum();

        if ($mode !== 'recipe' && $measuredOut <= 0) {
            $errors[] = 'No dispatch transfer weighed for this batch yet, so there is no measured output to harmonize to.';
        }

        $planned = collect();
        foreach ($lines->groupBy('idt_transfer_id') as $transferId => $weighing) {
            if (!isset($chains[$transferId]) || ($mode !== 'recipe' && $measuredOut <= 0)) {
                foreach ($weighing as $line) {
                    $planned->push($this->planned($line, (float) $line->quantity, $line->location_code));
                }
                continue;
            }

            $chain = $chains[$transferId];
            $share = $expectedTotal > 0 ? $measuredOut * $chain['expected_out'] / $expectedTotal : 0;
            $quantities = $this->quantities($chain, $mode, $share);

            foreach ($weighing as $line) {
                $q = $quantities[$line->id] ?? (float) $line->quantity;
                $planned->push($this->planned($line, $q, $chain['locations'][$line->id] ?? $line->location_code));
            }
        }

        return [
            'mode' => $mode,
            'lines' => $planned,
            'measured_in' => $measuredIn,
            'measured_out' => $measuredOut,
            'expected_out' => $expectedTotal,
            'errors' => $errors,
        ];
    }

    /**
     * Save a plan, then (when asked and the write target is ProductionData) release the batch to BC.
     */
    public function apply(string $batchNo, string $packedItem, string $mode, bool $release, string $username): array
    {
        // Match the batch's orders to BC plans first (manual choices are kept).
        $orderNos = DB::table('generated_production_orders')->where('batch_no', $batchNo)->where('packed_item', $packedItem)
            ->distinct()->pluck('production_order_no')->all();
        $matched = app(PlanMatcher::class)->autoMatch($orderNos);

        $plan = $this->plan($batchNo, $packedItem, $mode);
        $now = now();
        $changed = 0;

        DB::transaction(function () use ($plan, $mode, $now, &$changed) {
            foreach ($plan['lines'] as $line) {
                if ($line->locked) {
                    continue;
                }
                $update = [
                    'harmonize_mode' => $mode,
                    'harmonized_at' => $now,
                    'updated_at' => $now,
                ];
                if (abs($line->new_quantity - $line->quantity) > 0.000005 || $line->new_location !== $line->location_code) {
                    $changed++;
                }
                $update['quantity'] = round($line->new_quantity, 5);
                $update['location_code'] = $line->new_location;
                DB::table('generated_production_orders')->where('id', $line->id)
                    ->whereNull('production_data_at')->whereNull('bc_staged_at')->whereNull('released_at')->where('published', 0)
                    ->update($update + (is_null($line->recipe_quantity) ? ['recipe_quantity' => $line->quantity] : []));
            }
        });

        $written = $release ? $this->release($batchNo, $packedItem, $username) : '';

        return ['changed' => $changed, 'errors' => $plan['errors'], 'written' => $written, 'plan' => $plan, 'matched' => $matched];
    }

    /**
     * Send the batch's open orders to BC, each by one route: plan-matched orders over the plan route when
     * production_orders.planned_route is 'plan' (BC nets the plan), everything else to ProductionData.
     */
    public function release(string $batchNo, string $packedItem, string $username): string
    {
        $open = DB::table('generated_production_orders')
            ->where('batch_no', $batchNo)->where('packed_item', $packedItem)
            ->whereNull('production_data_at')->whereNull('bc_staged_at')->whereNull('released_at')->where('published', 0)
            ->get();
        if ($open->isEmpty()) {
            return ', nothing left to release';
        }

        $viaPlan = config('production_orders.planned_route') === 'plan';
        $byOrder = $open->groupBy('production_order_no');
        $planOrders = $viaPlan
            ? $byOrder->filter(fn($lines) => $lines->first()->plan_order_no && $lines->first()->transaction_id)->keys()->all()
            : [];
        $pdRows = $byOrder->except($planOrders)->flatten(1)->all();
        $msg = '';

        if ($planOrders) {
            DB::table('generated_production_orders')->whereIn('production_order_no', $planOrders)
                ->update(['released_at' => now(), 'released_to' => 'plan', 'updated_at' => now()]);
            $r = app(\App\Services\BcPlans\ExecutionPublisher::class)->publishNow($planOrders);
            $msg .= ', ' . $r['staged'] . ' of ' . count($planOrders) . ' order(s) staged to BC with their plan'
                . ($r['rejected'] + $r['errors'] ? ' (' . ($r['rejected'] + $r['errors']) . ' refused, see the order status)' : '');
        }

        if ($pdRows) {
            $msg .= $this->writer->write($pdRows, $username);
            $orderNos = collect($pdRows)->pluck('production_order_no')->unique()->all();
            if ($this->writer->enabled()) {
                DB::table('generated_production_orders')->whereIn('production_order_no', $orderNos)->whereNotNull('production_data_at')
                    ->whereNull('released_at')->update(['released_at' => now(), 'released_to' => 'production_data', 'updated_at' => now()]);
            } else {
                DB::table('generated_production_orders')->whereIn('production_order_no', $orderNos)
                    ->update(['released_at' => now(), 'released_to' => 'local', 'updated_at' => now()]);
                $msg .= ', released locally (write target is local)';
            }
        }

        return $msg;
    }

    /**
     * The chain of steps one weighing produced, with the recipe figures each line is scaled by.
     */
    private function chain(Collection $weighing, array $recipeRows): array
    {
        $first = $weighing->first();
        $netWeight = (float) $first->net_weight;
        $carried = $first->weighed_item;
        $steps = [];
        $locations = [];

        foreach ($weighing->groupBy('step')->sortKeys() as $step => $stepLines) {
            $output = $stepLines->firstWhere('line_type', 'output');
            if (!$output) {
                throw new \RuntimeException("step {$step} has no output line.");
            }
            $recipe = $recipeRows[$output->recipe][$output->item_no] ?? null;
            if (!$recipe) {
                throw new \RuntimeException("recipe {$output->recipe} for {$output->item_no} is no longer in the recipe table.");
            }
            $anchor = $recipe['inputs'][$carried] ?? null;
            if (!$anchor || $anchor['qty'] <= 0 || $recipe['batch_size'] <= 0) {
                throw new \RuntimeException("recipe {$output->recipe} has no usable quantity for {$carried}.");
            }

            $consumption = [];
            foreach ($stepLines->where('line_type', 'consumption') as $line) {
                $in = $recipe['inputs'][$line->item_no] ?? null;
                if (!$in) {
                    throw new \RuntimeException("recipe {$output->recipe} no longer lists {$line->item_no}.");
                }
                $consumption[$line->id] = ['qty_per' => $in['qty'], 'carried' => $line->item_no === $carried];
                $locations[$line->id] = $in['location'] ?: $line->location_code;
            }
            $locations[$output->id] = $recipe['output_location'] ?: $output->location_code;

            $steps[] = [
                'output_id' => $output->id,
                'batch_size' => $recipe['batch_size'],
                'anchor_qty' => $anchor['qty'],
                'consumption' => $consumption,
            ];
            $carried = $output->item_no;
        }

        // Expected output on recipe yield, forward from the stuffing weight
        $qty = $netWeight;
        foreach ($steps as $s) {
            $qty = $s['batch_size'] * $qty / $s['anchor_qty'];
        }

        return ['net_weight' => $netWeight, 'steps' => $steps, 'expected_out' => $qty, 'locations' => $locations];
    }

    /**
     * New quantity per line id for one weighing.
     */
    private function quantities(array $chain, string $mode, float $measuredOut): array
    {
        $steps = $chain['steps'];
        $n = count($steps);
        $q = [];

        // Forward scale per step from the measured input (recipe yields)
        $forward = [];
        $carried = $chain['net_weight'];
        foreach ($steps as $i => $s) {
            $forward[$i] = $carried / $s['anchor_qty'];
            $carried = $s['batch_size'] * $forward[$i];
        }

        if ($mode === 'recipe') {
            foreach ($steps as $i => $s) {
                $q[$s['output_id']] = $s['batch_size'] * $forward[$i];
                foreach ($s['consumption'] as $id => $c) {
                    $q[$id] = $c['qty_per'] * $forward[$i];
                }
            }

            return $q;
        }

        if ($mode === 'output_at_last') {
            foreach ($steps as $i => $s) {
                $last = $i === $n - 1;
                $q[$s['output_id']] = $last ? $measuredOut : $s['batch_size'] * $forward[$i];
                foreach ($s['consumption'] as $id => $c) {
                    // On the last step what goes in is still what the previous step made; the packaging and
                    // other materials follow what came out.
                    $scale = ($last && !$c['carried']) ? $measuredOut / $s['batch_size'] : $forward[$i];
                    $q[$id] = $c['qty_per'] * $scale;
                }
            }

            return $q;
        }

        // output_at_first: back from the measured output. Each step's output is what the next one consumed.
        $backward = [];
        $out = $measuredOut;
        for ($i = $n - 1; $i >= 0; $i--) {
            $backward[$i] = $out / $steps[$i]['batch_size'];
            $q[$steps[$i]['output_id']] = $out;
            $out = $steps[$i]['anchor_qty'] * $backward[$i];
        }
        foreach ($steps as $i => $s) {
            foreach ($s['consumption'] as $id => $c) {
                // The first step keeps its measured input, and the casings and spices fed with it.
                $q[$id] = $c['qty_per'] * ($i === 0 ? $forward[0] : $backward[$i]);
            }
        }

        return $q;
    }

    private function recipeRows(array $recipes): array
    {
        if (!$recipes) {
            return [];
        }

        $table = config('production_orders.recipe_table', config('recipes.table', 'RecipeData'));
        $out = [];
        DB::table($table)->whereIn('recipe', $recipes)->whereNotNull('input_item')
            ->select('recipe', 'output_item', 'output_item_location', 'batch_size', 'input_item', 'input_item_qt_per', 'input_item_location')
            ->get()
            ->each(function ($r) use (&$out) {
                $key = &$out[$r->recipe][$r->output_item];
                $key['batch_size'] = $key['batch_size'] ?? (float) $r->batch_size;
                $key['output_location'] = $key['output_location'] ?? $r->output_item_location;
                // first row per input wins, as the generator's unique('input_item') does
                if (!isset($key['inputs'][$r->input_item])) {
                    $key['inputs'][$r->input_item] = ['qty' => (float) $r->input_item_qt_per, 'location' => $r->input_item_location];
                }
                unset($key);
            });

        return $out;
    }

    private function planned($line, float $quantity, ?string $location): object
    {
        $p = clone $line;
        $p->locked = !is_null($line->production_data_at ?? null) || !is_null($line->bc_staged_at ?? null)
            || !is_null($line->released_at ?? null) || (int) $line->published === 1;
        $p->new_quantity = $p->locked ? (float) $line->quantity : round($quantity, 5);
        $p->new_location = $p->locked ? $line->location_code : ($location ?: $line->location_code);

        return $p;
    }

    /**
     * Unattended run: harmonize (and release) every open batch between the dates that has a measured
     * output. Used by stuffing:harmonize at the end of the day.
     */
    public function run(string $from, string $to, string $mode, bool $release, string $username, ?callable $onEach = null): array
    {
        $summary = ['batches' => 0, 'applied' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($this->batches($from, $to, null, 'open') as $b) {
            $summary['batches']++;
            if ($mode !== 'recipe' && $b->dispatched_kg <= 0) {
                $summary['skipped']++;
                $onEach && $onEach($b, 'skipped: nothing dispatched yet');
                continue;
            }
            try {
                $r = $this->apply($b->batch_no, $b->packed_item, $mode, $release, $username);
                $summary['applied']++;
                $onEach && $onEach($b, $r['changed'] . ' line(s) changed' . $r['written'] . ($r['errors'] ? '; ' . implode('; ', $r['errors']) : ''));
            } catch (\Exception $e) {
                $summary['failed']++;
                $onEach && $onEach($b, 'failed: ' . $e->getMessage());
            }
        }

        return $summary;
    }
}
