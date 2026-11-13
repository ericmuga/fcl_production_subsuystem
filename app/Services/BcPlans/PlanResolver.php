<?php

namespace App\Services\BcPlans;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Finds the open BC plan an execution order fulfils (integration map S8, CP1): same item, same location, same day.
 * There is no carry-over to the next day's plan (CP4): no open plan means the order goes to BC without a plan no.
 * and is flagged for the daily exception report.
 */
class PlanResolver
{
    private $cache = [];

    public function openPlanFor(string $itemNo, ?string $locationCode, string $date): ?string
    {
        $key = $itemNo . '|' . $locationCode . '|' . $date;
        if (!array_key_exists($key, $this->cache)) {
            try {
                $this->cache[$key] = DB::table('bc_plan_orders')
                    ->where('item_no', $itemNo)
                    ->where('location_code', (string) $locationCode)
                    ->where('plan_date', $date)
                    ->where('status', 'open')
                    ->orderBy('plan_no')
                    ->value('plan_no');
            } catch (QueryException $e) {
                // Plan tables not migrated yet: generating execution orders must never fail because of the plan.
                Log::warning('BC plan lookup unavailable: ' . $e->getMessage());
                $this->cache[$key] = null;
            }
        }
        return $this->cache[$key];
    }

    /**
     * The BC batch a WMS production order fulfils: every WMS order is one batch of the released order. The same WMS
     * order always gets the same batch (safe on retries); otherwise the next pushed batch no WMS order has taken yet.
     * Returns null when the plan has no free batch (output above plan, or batches not pushed): the order still carries
     * the plan no. and BC nets it against the plan.
     */
    public function batchFor(?string $planNo, string $wmsOrderNo): ?object
    {
        if (!$planNo) {
            return null;
        }
        try {
            return DB::transaction(function () use ($planNo, $wmsOrderNo) {
                $mine = DB::table('bc_plan_batches')->where('plan_no', $planNo)->where('wms_order_no', $wmsOrderNo)->first();
                if ($mine) {
                    return $mine;
                }
                $next = DB::table('bc_plan_batches')->lock('WITH (UPDLOCK, READPAST)')
                    ->where('plan_no', $planNo)->whereNull('wms_order_no')
                    ->whereIn('bc_status', ['sent', 'in_progress'])->where('wms_status', 'pending')
                    ->orderBy('plan_line_no')->orderBy('batch_no')->first();
                if (!$next) {
                    return null;
                }
                DB::table('bc_plan_batches')->where('id', $next->id)->update([
                    'wms_order_no' => $wmsOrderNo, 'wms_status' => 'in_progress', 'updated_at' => now(),
                ]);
                return DB::table('bc_plan_batches')->where('id', $next->id)->first();
            });
        } catch (QueryException $e) {
            Log::warning('BC batch lookup unavailable: ' . $e->getMessage());
            return null;
        }
    }

    /** The WMS order for a batch is done: the batch shows completed in WMS until BC confirms the posting. */
    public static function completeBatch(?string $batchCode): void
    {
        if ($batchCode) {
            DB::table('bc_plan_batches')->where('batch_code', $batchCode)->where('wms_status', '<>', 'completed')
                ->update(['wms_status' => 'completed', 'updated_at' => now()]);
        }
    }

    /** Item allowed on the plan: its output or one of its components / by-products (S7, CP6). Warn only (CP24). */
    public function isItemOnPlan(string $planNo, string $itemNo): bool
    {
        return DB::table('bc_plan_orders')->where('plan_no', $planNo)->where('item_no', $itemNo)->exists()
            || DB::table('bc_plan_order_components')->where('plan_no', $planNo)->where('item_no', $itemNo)->exists();
    }

    /** Deterministic idempotency key for a WMS execution order: a replay sends the same id (CP19, CP23). */
    public static function transactionId(string $kind, string $orderNo): string
    {
        return strtoupper($kind) . ':' . $orderNo;
    }
}
