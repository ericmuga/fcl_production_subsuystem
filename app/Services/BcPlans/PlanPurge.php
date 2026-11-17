<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Purge of BC orders nobody has acted on, e.g. before running BC's regenerative plan again.
 *
 * - candidates(): orders WMS holds (firm planned, released, open) with what WMS has done on each; an order with any
 *   weighing, execution order, started batch or executed quantity is actioned and is never purged.
 * - request(): WMS checks again, stops weighing on the order ('purging') and asks BC to delete it
 *   (WMS Plan Close Request, Request Type Delete). BC checks its own side (ledger entries, journals, picks) too.
 * - refresh(): reads BC's answer. Deleted -> 'withdrawn'; kept by BC -> back to its status with BC's reason.
 *   The plan sync does the same when it sees the order gone from BC.
 * - forget(): removes withdrawn orders that were never acted on from WMS's own tables (orders BC deleted, whether
 *   from this page or from BC's "Delete Unactioned Production Orders" report).
 */
class PlanPurge
{
    public const PURGEABLE = ['firm_planned', 'released', 'open'];

    /** Execution tables that carry the plan they fulfil (migration add_plan_order_no_to_execution_tables). */
    private const EXECUTION_TABLES = ['generated_production_orders', 'idt_transfers', 'beheading_data', 'butchery_data', 'deboned_data', 'beef_slicing'];

    private $staging;

    public function __construct(?BcStaging $staging = null)
    {
        $this->staging = $staging;
    }

    private function staging(): BcStaging
    {
        return $this->staging ?? ($this->staging = new BcStaging());
    }

    /**
     * Orders within the filters with their WMS activity. Filters: from, to (plan date), section (PlanProcess value),
     * statuses (default PURGEABLE + purging + withdrawn).
     */
    public function candidates(array $filters = [])
    {
        $statuses = $filters['statuses'] ?? array_merge(self::PURGEABLE, ['purging', 'withdrawn']);
        $plans = DB::table('bc_plan_orders AS p')
            ->whereIn('p.status', $statuses)
            ->when($filters['from'] ?? null, fn($q, $v) => $q->where('p.plan_date', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->where('p.plan_date', '<=', $v))
            ->tap(fn($q) => PlanProcess::filter($q, $filters['section'] ?? null))
            ->orderBy('p.plan_date')->orderBy('p.section')->orderBy('p.item_no')
            ->get();
        $activity = $this->activity($plans->pluck('plan_no')->all());
        foreach ($plans as $p) {
            $a = $activity[$p->plan_no] ?? [];
            if ((float) $p->executed_qty != 0) {
                $a[] = 'BC executed ' . rtrim(rtrim(number_format((float) $p->executed_qty, 3, '.', ''), '0'), '.') . ' ' . $p->uom;
            }
            $p->activity = $a;
            $p->actioned = (bool) $a;
        }
        return $plans;
    }

    /** plan no => list of what WMS did on it (empty: never acted on). */
    public function activity(array $planNos): array
    {
        $out = [];
        foreach (array_chunk(array_values(array_unique($planNos)), 1000) as $chunk) {
            foreach (DB::table('plan_batch_weighings')->whereIn('plan_no', $chunk)->groupBy('plan_no')
                ->select('plan_no', DB::raw('COUNT(*) AS n'))->get() as $r) {
                $out[$r->plan_no][] = $r->n . ' weighing(s)';
            }
            foreach (DB::table('bc_plan_batches')->whereIn('plan_no', $chunk)
                ->where(fn($q) => $q->whereNotNull('wms_order_no')->orWhereIn('wms_status', ['in_progress', 'completed']))
                ->groupBy('plan_no')->select('plan_no', DB::raw('COUNT(*) AS n'))->get() as $r) {
                $out[$r->plan_no][] = $r->n . ' batch(es) started';
            }
            foreach (self::EXECUTION_TABLES as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'plan_order_no')) {
                    continue;
                }
                foreach (DB::table($table)->whereIn('plan_order_no', $chunk)->groupBy('plan_order_no')
                    ->select('plan_order_no', DB::raw('COUNT(*) AS n'))->get() as $r) {
                    $out[$r->plan_order_no][] = $r->n . ' ' . str_replace('_', ' ', $table) . ' line(s)';
                }
            }
        }
        return $out;
    }

    /** Ask BC to delete these orders. @return array<string, array{ok:bool,message:string}> per order */
    public function request(array $planNos, ?int $userId, ?string $username): array
    {
        $results = [];
        $activity = $this->activity($planNos);
        // Orders already deleted in BC (e.g. by BC's "Delete Unactioned Production Orders") need no request.
        $inBc = array_flip($this->staging()->existingOrders($planNos));
        foreach (array_unique($planNos) as $no) {
            $plan = DB::table('bc_plan_orders')->where('plan_no', $no)->first();
            if (!$plan || !in_array($plan->status, self::PURGEABLE, true)) {
                $results[$no] = ['ok' => false, 'message' => $plan ? 'It is ' . str_replace('_', ' ', $plan->status) . '.' : 'Not found.'];
                continue;
            }
            if (!empty($activity[$no]) || (float) $plan->executed_qty != 0) {
                $results[$no] = ['ok' => false, 'message' => 'Acted on: ' . implode(', ', $activity[$no] ?? ['BC executed quantity']) . '.'];
                continue;
            }
            if (!isset($inBc[$no])) {
                $this->markWithdrawn($no, 'Already deleted in BC.');
                $results[$no] = ['ok' => true, 'message' => 'Already deleted in BC; withdrawn in WMS.', 'withdrawn' => true];
                continue;
            }
            // Stop weighing first, so nothing can start between the check and BC's delete.
            $locked = DB::table('bc_plan_orders')->where('plan_no', $no)->where('status', $plan->status)->update([
                'status' => 'purging', 'purge_from_status' => $plan->status, 'purge_requested_at' => now(),
                'purge_requested_by' => $userId, 'purge_message' => 'Delete requested; BC deletes it on its next job run.', 'updated_at' => now(),
            ]);
            if (!$locked) {
                $results[$no] = ['ok' => false, 'message' => 'Changed while checking; try again.'];
                continue;
            }
            if (DB::table('plan_batch_weighings')->where('plan_no', $no)->exists()) {
                $this->restore($no, $plan->status, 'Kept: a weighing started while the delete was being requested.');
                $results[$no] = ['ok' => false, 'message' => 'A weighing started meanwhile; kept.'];
                continue;
            }
            try {
                $this->staging()->requestDelete($no, $username);
                $results[$no] = ['ok' => true, 'message' => 'Delete requested.'];
            } catch (\Throwable $e) {
                $this->restore($no, $plan->status, mb_substr('Delete request failed: ' . $e->getMessage(), 0, 250));
                $results[$no] = ['ok' => false, 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    /** Read BC's answer for orders being purged. @return array{deleted:int,kept:int} */
    public function refresh(): array
    {
        $purging = DB::table('bc_plan_orders')->where('status', 'purging')->pluck('purge_from_status', 'plan_no')->all();
        $done = ['deleted' => 0, 'kept' => 0];
        if (!$purging) {
            return $done;
        }
        $statuses = $this->staging()->closeRequestStatuses(array_keys($purging), 1);
        foreach ($statuses as $no => $s) {
            if ($s['status'] === 'completed') {
                $this->markWithdrawn($no, 'Deleted in BC.');
                $done['deleted']++;
            } elseif ($s['status'] === 'held') {
                $this->restore($no, $purging[$no] ?: 'released', mb_substr('BC kept it: ' . preg_replace('/^WMS_PURGE_KEPT:\s*/', '', $s['message']), 0, 250));
                $done['kept']++;
            } else {
                $msg = $s['status'] === 'error' ? 'BC could not delete it yet (retrying): ' . $s['message'] : 'BC: ' . $s['status'] . '.';
                DB::table('bc_plan_orders')->where('plan_no', $no)->update(['purge_message' => mb_substr($msg, 0, 250), 'updated_at' => now()]);
            }
        }
        return $done;
    }

    /**
     * Remove withdrawn orders that were never acted on from WMS (order, components, batches). Orders with any WMS
     * activity stay for the record. @return int orders removed
     */
    public function forget(array $planNos = [], array $filters = []): int
    {
        $query = DB::table('bc_plan_orders')->where('status', 'withdrawn')
            ->when($planNos, fn($q) => $q->whereIn('plan_no', $planNos))
            ->when($filters['from'] ?? null, fn($q, $v) => $q->where('plan_date', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->where('plan_date', '<=', $v))
            ->tap(fn($q) => PlanProcess::filter($q, $filters['section'] ?? null, 'bc_plan_orders'));
        $plans = $query->get(['plan_no', 'executed_qty']);
        $activity = $this->activity($plans->pluck('plan_no')->all());
        $gone = $plans->filter(fn($p) => empty($activity[$p->plan_no]) && (float) $p->executed_qty == 0)->pluck('plan_no')->all();
        foreach (array_chunk($gone, 1000) as $chunk) {
            DB::transaction(function () use ($chunk) {
                DB::table('bc_plan_batches')->whereIn('plan_no', $chunk)->delete();
                DB::table('bc_plan_order_components')->whereIn('plan_no', $chunk)->delete();
                DB::table('bc_plan_orders')->whereIn('plan_no', $chunk)->where('status', 'withdrawn')->delete();
            });
        }
        return count($gone);
    }

    private function markWithdrawn(string $no, string $message): void
    {
        DB::transaction(function () use ($no, $message) {
            DB::table('bc_plan_orders')->where('plan_no', $no)->update([
                'status' => 'withdrawn', 'purge_message' => $message, 'payload_hash' => null, 'synced_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('bc_plan_batches')->where('plan_no', $no)->where('wms_status', 'pending')
                ->update(['wms_status' => 'cancelled', 'updated_at' => now()]);
        });
    }

    private function restore(string $no, string $status, string $message): void
    {
        DB::table('bc_plan_orders')->where('plan_no', $no)->where('status', 'purging')->update([
            'status' => $status, 'purge_message' => $message, 'payload_hash' => null, 'updated_at' => now(),
        ]);
    }
}
