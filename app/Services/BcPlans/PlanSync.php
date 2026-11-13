<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Mirrors BC's released orders (plans) and their WMS batches into bc_plan_orders / bc_plan_order_components /
 * bc_plan_batches.
 * - Idempotent: a plan whose content has not changed is left alone (payload hash), so re-running changes nothing.
 * - syncChanged(): incremental, reads only what BC inserted or updated since the last run (watermark in bc_sync_state),
 *   then checks WMS's released/open plans still exist in BC (finished -> closed, deleted -> withdrawn).
 * - sync($from, $to): full sync of a date window; a plan BC no longer lists in the window becomes 'withdrawn'.
 * - Batches keep their WMS-side fields (wms_status, wms_order_no); BC fields are refreshed.
 */
class PlanSync
{
    private const STATE_KEY = 'plans';
    private const OVERLAP_SECONDS = 120;

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

    /** @return array{fetched:int,created:int,updated:int,unchanged:int,withdrawn:int,batches:int} */
    public function sync(string $from, string $to, bool $dryRun = false): array
    {
        $plans = $this->source->fetch($from, $to);
        $stats = $this->newStats(count($plans));
        DB::transaction(function () use ($plans, $from, $to, $dryRun, &$stats) {
            $seen = $this->upsertPlans($plans, $dryRun, $stats);
            $gone = DB::table('bc_plan_orders')->whereBetween('plan_date', [$from, $to])->whereIn('status', ['open', 'released', 'firm_planned'])
                ->when($seen, function ($q) use ($seen) {
                    foreach (array_chunk($seen, 1000) as $chunk) {
                        $q->whereNotIn('plan_no', $chunk);
                    }
                });
            $stats['withdrawn'] = (clone $gone)->count();
            if (!$dryRun && $stats['withdrawn']) {
                $gone->update(['status' => 'withdrawn', 'payload_hash' => null, 'synced_at' => now(), 'updated_at' => now()]);
            }
        });
        return $stats;
    }

    /**
     * Incremental sync of inserts and updates since the last run. $full ignores the watermark (first run / repair).
     * @return array{fetched:int,created:int,updated:int,unchanged:int,withdrawn:int,closed:int,batches:int,since:?string,watermark:?string}
     */
    public function syncChanged(bool $dryRun = false, bool $full = false): array
    {
        if (!method_exists($this->source, 'fetchChanged')) {
            throw new \RuntimeException('Incremental sync needs the SQL source; use a date window with the API source.');
        }
        $state = DB::table('bc_sync_state')->where('key', self::STATE_KEY)->first();
        $since = (!$full && $state && $state->watermark)
            ? now()->parse($state->watermark)->subSeconds(self::OVERLAP_SECONDS)->format('Y-m-d H:i:s.v')
            : null;
        $res = $this->source->fetchChanged($since);
        $stats = $this->newStats(count($res['plans'])) + ['closed' => 0, 'converted' => 0, 'since' => $since, 'watermark' => $res['watermark']];

        DB::transaction(function () use ($res, $dryRun, &$stats) {
            $seen = $this->upsertPlans($res['plans'], $dryRun, $stats);

            // Orders that left the plan set without a change we can see (finished without a push, or deleted).
            $active = DB::table('bc_plan_orders')->whereIn('status', ['open', 'released', 'firm_planned'])
                ->when($seen, fn($q) => $q->whereNotIn('plan_no', $seen))->pluck('status', 'plan_no')->all();
            if ($active && method_exists($this->source, 'bcStates')) {
                $states = $this->source->bcStates(array_keys($active));
                foreach ($active as $no => $wmsStatus) {
                    $s = $states[$no] ?? null;
                    if ($s === 'released' || $s === 'firm_planned') {
                        continue;
                    }
                    // A firm planned order that disappears was released under a new no. (released_from links it).
                    if ($s === 'finished') {
                        $new = 'closed';
                        $stats['closed']++;
                    } elseif ($wmsStatus === 'firm_planned') {
                        $new = 'converted';
                        $stats['converted']++;
                    } else {
                        $new = 'withdrawn';
                        $stats['withdrawn']++;
                    }
                    if (!$dryRun) {
                        DB::table('bc_plan_orders')->where('plan_no', $no)->update([
                            'status' => $new, 'payload_hash' => null, 'synced_at' => now(), 'updated_at' => now(),
                        ]);
                        DB::table('bc_plan_batches')->where('plan_no', $no)->whereIn('wms_status', ['pending'])
                            ->update(['wms_status' => 'cancelled', 'updated_at' => now()]);
                    }
                }
            }

            if (!$dryRun) {
                DB::table('bc_sync_state')->updateOrInsert(['key' => self::STATE_KEY], [
                    'watermark' => $res['watermark'], 'last_run_at' => now(),
                    'last_stats' => json_encode(array_diff_key($stats, ['since' => 1, 'watermark' => 1])), 'updated_at' => now(),
                ]);
            }
        });
        return $stats;
    }

    private function newStats(int $fetched): array
    {
        return ['fetched' => $fetched, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'withdrawn' => 0, 'batches' => 0];
    }

    /** @return string[] plan nos seen */
    private function upsertPlans(array $plans, bool $dryRun, array &$stats): array
    {
        $now = now();
        $seen = [];
        $existing = [];
        foreach (array_chunk(array_column($plans, 'plan_no'), 1000) as $chunk) {
            $existing += DB::table('bc_plan_orders')->whereIn('plan_no', $chunk)->pluck('payload_hash', 'plan_no')->all();
        }
        foreach ($plans as $p) {
            $seen[] = $p['plan_no'];
            $hash = hash('sha256', json_encode($this->hashable($p)));
            $known = array_key_exists($p['plan_no'], $existing);
            if ($known && $existing[$p['plan_no']] === $hash) {
                $stats['unchanged']++;
                continue;
            }
            $stats[$known ? 'updated' : 'created']++;
            $stats['batches'] += count($p['batches'] ?? []);
            if ($dryRun) {
                continue;
            }
            $row = [
                'item_no' => $p['item_no'],
                'description' => mb_substr((string) $p['description'], 0, 100),
                'location_code' => $p['location_code'],
                'plan_date' => $p['plan_date'],
                'uom' => $p['uom'],
                'batch_uom' => $p['batch_uom'] ?? null,
                'section' => $p['section'] ?? null,
                'sections' => isset($p['sections']) ? mb_substr((string) $p['sections'], 0, 150) : null,
                'released_from' => $p['released_from'] ?? null,
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
            if ($known) {
                DB::table('bc_plan_orders')->where('plan_no', $p['plan_no'])->update($row);
            } else {
                DB::table('bc_plan_orders')->insert($row + ['plan_no' => $p['plan_no'], 'created_at' => $now]);
            }
            DB::table('bc_plan_order_components')->where('plan_no', $p['plan_no'])->delete();
            $components = array_map(fn($c) => [
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
            ], $p['components']);
            foreach (array_chunk($components, 100) as $chunk) {
                DB::table('bc_plan_order_components')->insert($chunk);
            }
            $this->upsertBatches($p, $now);
        }
        return $seen;
    }

    private function upsertBatches(array $p, $now): void
    {
        foreach ($p['batches'] ?? [] as $b) {
            $bc = [
                'plan_no' => $p['plan_no'],
                'plan_line_no' => $b['plan_line_no'],
                'batch_no' => $b['batch_no'],
                'item_no' => $b['item_no'],
                'variant_code' => $b['variant_code'],
                'location_code' => $b['location_code'],
                'due_date' => $b['due_date'],
                'batch_uom' => $b['batch_uom'],
                'quantity' => $b['quantity'],
                'base_uom' => $b['base_uom'],
                'quantity_base' => $b['quantity_base'],
                'bc_executed_qty_base' => $b['bc_executed_qty_base'],
                'bc_status' => $b['bc_status'],
                'pushed_at' => $b['pushed_at'],
                'bc_completed_at' => $b['bc_completed_at'],
                'synced_at' => $now,
                'updated_at' => $now,
            ];
            $current = DB::table('bc_plan_batches')->where('batch_code', $b['batch_code'])->first();
            if (!$current) {
                $wms = ['cancelled' => 'cancelled', 'completed' => 'completed', 'in_progress' => 'in_progress'][$b['bc_status']] ?? 'pending';
                DB::table('bc_plan_batches')->insert($bc + ['batch_code' => $b['batch_code'], 'wms_status' => $wms, 'created_at' => $now]);
                continue;
            }
            // BC is the authority on cancellation and on posted completion; otherwise WMS keeps its own progress.
            if ($b['bc_status'] === 'cancelled' && $current->wms_status === 'pending') {
                $bc['wms_status'] = 'cancelled';
            } elseif ($b['bc_status'] === 'completed') {
                $bc['wms_status'] = 'completed';
            } elseif ($b['bc_status'] === 'sent' && $current->wms_status === 'cancelled') {
                $bc['wms_status'] = 'pending'; // re-cut after a plan increase brought the batch back
            }
            DB::table('bc_plan_batches')->where('id', $current->id)->update($bc);
        }
    }

    private function hashable(array $p): array
    {
        unset($p['bc_modified_at'], $p['bc_id']);
        return $p;
    }
}
