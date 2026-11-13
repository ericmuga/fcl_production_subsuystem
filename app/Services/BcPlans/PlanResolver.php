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
