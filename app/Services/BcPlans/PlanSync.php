<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Mirrors BC's daily plans into bc_plan_orders / bc_plan_order_components (integration map S4-S5).
 * Idempotent: a plan whose content has not changed is left alone (payload hash), so re-running a sync changes nothing.
 * A plan that BC no longer publishes inside the synced window becomes 'withdrawn'; one already closed stays closed.
 */
class PlanSync
{
    private $source;

    public function __construct(PlanSource $source)
    {
        $this->source = $source;
    }

    public static function make(?string $source = null): self
    {
        $source = $source ?? config('bc_plan.source', 'sql');
        return new self($source === 'api' ? new ApiPlanSource() : new SqlPlanSource());
    }

    public function source(): PlanSource
    {
        return $this->source;
    }

    /** @return array{fetched:int,created:int,updated:int,unchanged:int,withdrawn:int} */
    public function sync(string $from, string $to, bool $dryRun = false): array
    {
        $plans = $this->source->fetch($from, $to);
        $stats = ['fetched' => count($plans), 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'withdrawn' => 0];
        $now = now();
        $seen = [];

        DB::transaction(function () use ($plans, $from, $to, $dryRun, $now, &$stats, &$seen) {
            $existing = DB::table('bc_plan_orders')->whereIn('plan_no', array_column($plans, 'plan_no') ?: [''])
                ->pluck('payload_hash', 'plan_no');
            foreach ($plans as $p) {
                $seen[] = $p['plan_no'];
                $hash = hash('sha256', json_encode($this->hashable($p)));
                if (isset($existing[$p['plan_no']]) && $existing[$p['plan_no']] === $hash) {
                    $stats['unchanged']++;
                    continue;
                }
                $stats[isset($existing[$p['plan_no']]) ? 'updated' : 'created']++;
                if ($dryRun) {
                    continue;
                }
                $row = [
                    'item_no' => $p['item_no'],
                    'description' => mb_substr((string) $p['description'], 0, 100),
                    'location_code' => $p['location_code'],
                    'plan_date' => $p['plan_date'],
                    'uom' => $p['uom'],
                    'planned_qty' => $p['planned_qty'],
                    'executed_qty' => $p['executed_qty'],
                    'remaining_qty' => $p['remaining_qty'],
                    'status' => $p['status'],
                    'source' => $this->source->name(),
                    'bc_modified_at' => $p['bc_modified_at'],
                    'closed_at' => $p['closed_at'],
                    'closed_by' => $p['closed_by'],
                    'payload_hash' => $hash,
                    'synced_at' => $now,
                    'updated_at' => $now,
                ];
                if (isset($existing[$p['plan_no']])) {
                    DB::table('bc_plan_orders')->where('plan_no', $p['plan_no'])->update($row);
                } else {
                    DB::table('bc_plan_orders')->insert($row + ['plan_no' => $p['plan_no'], 'created_at' => $now]);
                }
                DB::table('bc_plan_order_components')->where('plan_no', $p['plan_no'])->delete();
                $components = array_map(function ($c) use ($p, $now) {
                    return [
                        'plan_no' => $p['plan_no'],
                        'plan_line_no' => $c['plan_line_no'],
                        'line_no' => $c['line_no'],
                        'item_no' => $c['item_no'],
                        'description' => mb_substr((string) $c['description'], 0, 100),
                        'uom' => $c['uom'],
                        'qty_per' => $c['qty_per'],
                        'expected_qty' => $c['expected_qty'],
                        'remaining_qty' => $c['remaining_qty'],
                        'location_code' => $c['location_code'],
                        'flushing_method' => $c['flushing_method'],
                        'is_by_product' => $c['is_by_product'] ? 1 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $p['components']);
                foreach (array_chunk($components, 100) as $chunk) {
                    DB::table('bc_plan_order_components')->insert($chunk);
                }
            }

            // Plans in the window that BC no longer publishes
            $gone = DB::table('bc_plan_orders')->whereBetween('plan_date', [$from, $to])->where('status', 'open')
                ->when($seen, function ($q) use ($seen) {
                    foreach (array_chunk($seen, 1000) as $chunk) {
                        $q->whereNotIn('plan_no', $chunk);
                    }
                });
            $stats['withdrawn'] = (clone $gone)->count();
            if (!$dryRun && $stats['withdrawn']) {
                $gone->update(['status' => 'withdrawn', 'payload_hash' => null, 'synced_at' => $now, 'updated_at' => $now]);
            }
        });

        return $stats;
    }

    private function hashable(array $p): array
    {
        unset($p['bc_modified_at'], $p['bc_id']);
        return $p;
    }
}
