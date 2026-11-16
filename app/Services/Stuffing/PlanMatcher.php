<?php

namespace App\Services\Stuffing;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Matches draft (generated) production orders to BC plans. Each order is matched on its own output: the stuffing
 * order to the plan for the stuffed item, the packing order to the plan for the packed item.
 *
 * A candidate plan has the same item, is open or released, sits at the order's location (WMS location or BC
 * location) and is dated within the window around the production date. The best one is the same day, then the
 * nearest earlier day, then the nearest later day; among those, one with quantity still to produce first. A plan
 * at another location is offered for manual matching only.
 *
 * Orders already staged to BC or written to ProductionData keep their match. A manual choice (including "no
 * plan") is never overwritten by automatic matching.
 */
class PlanMatcher
{
    const MATCHABLE = ['open', 'released'];

    /** Lines that can still be matched: not sent to BC by either route. */
    private function openLines()
    {
        return DB::table('generated_production_orders')
            ->whereNull('bc_staged_at')->whereNull('production_data_at')->whereNull('released_at')->where('published', 0);
    }

    /**
     * Automatic matching for the given orders (or a batch + packed item).
     *
     * @return array{matched:int, unmatched:int, kept:int}
     */
    public function autoMatch(array $orderNos): array
    {
        $stats = ['matched' => 0, 'unmatched' => 0, 'kept' => 0];

        foreach ($this->outputs($orderNos) as $out) {
            if ($out->plan_matched_by === 'manual' || $out->plan_matched_by === 'none' || $this->sent($out->production_order_no)) {
                $stats['kept']++;
                continue;
            }
            $best = $this->candidates($out, false)->first();
            if (!$best) {
                if ($out->plan_order_no && $out->plan_matched_by !== 'generation') {
                    $this->assign($out->production_order_no, null, 'auto');
                }
                $stats['unmatched']++;
                continue;
            }
            if ($best->plan_no !== $out->plan_order_no) {
                $this->assign($out->production_order_no, $best->plan_no, 'auto');
            } else {
                DB::table('generated_production_orders')->where('production_order_no', $out->production_order_no)
                    ->whereNull('plan_matched_by')->update(['plan_matched_by' => 'generation', 'plan_matched_at' => now()]);
            }
            $stats['matched']++;
        }

        return $stats;
    }

    /** Manual choice: a plan no., or null for "no plan". */
    public function setManual(string $orderNo, ?string $planNo): void
    {
        if ($this->sent($orderNo)) {
            throw new \RuntimeException("Order {$orderNo} has already been sent to BC; its plan cannot change.");
        }
        if ($planNo !== null && !DB::table('bc_plan_orders')->where('plan_no', $planNo)->whereIn('status', self::MATCHABLE)->exists()) {
            throw new \RuntimeException("Plan {$planNo} is not open or released.");
        }
        $this->assign($orderNo, $planNo, $planNo === null ? 'none' : 'manual');
    }

    /** Clears a manual choice so automatic matching applies again. */
    public function reset(string $orderNo): void
    {
        if (!$this->sent($orderNo)) {
            DB::table('generated_production_orders')->where('production_order_no', $orderNo)
                ->update(['plan_matched_by' => null, 'updated_at' => now()]);
            $this->autoMatch([$orderNo]);
        }
    }

    /**
     * Plans an order could be matched to, best first. $wide also offers other locations and a wider window, for
     * the manual pick list.
     */
    public function candidates(object $out, bool $wide = true): Collection
    {
        $date = \Illuminate\Support\Carbon::parse($out->transaction_date);
        $before = (int) config('production_orders.plan_match_days_before', 1) + ($wide ? 2 : 0);
        $after = (int) config('production_orders.plan_match_days_after', 1) + ($wide ? 2 : 0);

        $plans = DB::table('bc_plan_orders')
            ->where('item_no', $out->item_no)
            ->whereIn('status', self::MATCHABLE)
            ->whereBetween('plan_date', [$date->copy()->subDays($before)->toDateString(), $date->copy()->addDays($after)->toDateString()])
            ->when(!$wide, fn($q) => $q->where(fn($w) => $w->where('location_code', (string) $out->location_code)
                ->orWhere('bc_location_code', (string) $out->location_code)))
            ->get(['plan_no', 'item_no', 'location_code', 'bc_location_code', 'plan_date', 'planned_qty', 'status', 'section']);

        if ($plans->isEmpty()) {
            return $plans;
        }

        $matched = DB::table('generated_production_orders')->where('line_type', 'output')
            ->whereIn('plan_order_no', $plans->pluck('plan_no')->all())
            ->where('production_order_no', '<>', $out->production_order_no)
            ->groupBy('plan_order_no')->select('plan_order_no', DB::raw('SUM(quantity) AS qty'))
            ->pluck('qty', 'plan_order_no');

        return $plans->map(function ($p) use ($out, $date, $matched) {
            $p->matched_qty = (float) ($matched[$p->plan_no] ?? 0);
            $p->open_qty = max((float) $p->planned_qty - $p->matched_qty, 0);
            $p->same_location = $p->location_code === $out->location_code || $p->bc_location_code === $out->location_code;
            $days = $date->diffInDays(\Illuminate\Support\Carbon::parse($p->plan_date), false);   // + later, - earlier
            // same day, then earlier days, then later days
            $p->rank = [$p->same_location ? 0 : 1, $days === 0 ? 0 : ($days < 0 ? 1 : 2), abs($days), $p->open_qty > 0 ? 0 : 1, $p->plan_no];
            $p->days = $days;
            return $p;
        })->sort(fn($a, $b) => $a->rank <=> $b->rank)->values();
    }

    /** Output line per production order. */
    public function outputs(array $orderNos): Collection
    {
        return collect($orderNos)->unique()->chunk(500)->flatMap(fn($c) => DB::table('generated_production_orders')
            ->whereIn('production_order_no', $c->all())->where('line_type', 'output')
            ->get(['production_order_no', 'item_no', 'location_code', 'transaction_date', 'quantity', 'plan_order_no', 'plan_matched_by',
                'batch_code', 'bc_staged_at', 'production_data_at', 'released_at', 'published', 'process', 'step']))
            ->values();
    }

    private function sent(string $orderNo): bool
    {
        return !$this->openLines()->where('production_order_no', $orderNo)->exists()
            && DB::table('generated_production_orders')->where('production_order_no', $orderNo)->exists();
    }

    /** Sets the plan on every line of the order and moves its BC batch claim with it. */
    private function assign(string $orderNo, ?string $planNo, string $by): void
    {
        DB::transaction(function () use ($orderNo, $planNo, $by) {
            // give back the batch the order held on its old plan
            DB::table('bc_plan_batches')->where('wms_order_no', $orderNo)->where('wms_status', 'in_progress')
                ->when($planNo, fn($q) => $q->where('plan_no', '<>', $planNo))
                ->update(['wms_order_no' => null, 'wms_status' => 'pending', 'updated_at' => now()]);

            $batch = $planNo ? (new \App\Services\BcPlans\PlanResolver())->batchFor($planNo, $orderNo) : null;

            DB::table('generated_production_orders')->where('production_order_no', $orderNo)->update([
                'plan_order_no' => $planNo,
                'plan_line_no' => $batch->plan_line_no ?? null,
                'plan_batch_no' => $batch->batch_no ?? null,
                'batch_code' => $batch->batch_code ?? null,
                'plan_matched_by' => $by,
                'plan_matched_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
