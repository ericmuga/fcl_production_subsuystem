<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Cuts WMS batch orders for BC orders that BC has not batched (source 'wms'), so WMS can work in batches before the
 * BC build with "Push to WMS" is deployed. Same rules as BC's CutBatches:
 * - one batch = the item's BATCH unit in kg (batch_size_base); no BATCH unit = the whole order is one batch;
 * - started / completed batches keep their size; the rest of the open quantity is spread over the pending batches
 *   (reused in order, extras cancelled, new ones added), so batch codes stay stable when the order changes;
 * - orders that are closed, withdrawn or converted (firm planned released under a new no.) lose their pending batches.
 * Batches of firm planned orders are shown but cannot be fulfilled until the order is released (BC nets output
 * against released orders only).
 */
class LocalBatcher
{
    /** @return array{orders:int,created:int,resized:int,cancelled:int} */
    public function run(?array $planNos = null): array
    {
        $stats = ['orders' => 0, 'created' => 0, 'resized' => 0, 'cancelled' => 0];
        $now = now();

        DB::transaction(function () use ($planNos, $now, &$stats) {
            // Orders no longer active: pending WMS-cut batches go.
            $stats['cancelled'] += DB::table('bc_plan_batches')->where('source', 'wms')->where('wms_status', 'pending')
                ->whereNull('wms_order_no')
                ->whereIn('plan_no', fn($q) => $q->from('bc_plan_orders')->select('plan_no')
                    ->whereNotIn('status', ['firm_planned', 'released', 'open']))
                ->update(['wms_status' => 'cancelled', 'updated_at' => $now]);

            $plans = DB::table('bc_plan_orders')->whereIn('status', ['firm_planned', 'released', 'open'])
                ->when($planNos, fn($q) => $q->whereIn('plan_no', $planNos))
                // BC batched it: BC's batches rule.
                ->whereNotExists(fn($q) => $q->from('bc_plan_batches AS b')->whereColumn('b.plan_no', 'bc_plan_orders.plan_no')->where('b.source', 'bc'))
                ->get();
            foreach ($plans as $plan) {
                $stats['orders']++;
                $this->cut($plan, $now, $stats);
            }
        });
        return $stats;
    }

    private function cut(object $plan, $now, array &$stats): void
    {
        $size = (float) $plan->batch_size_base > 0 ? (float) $plan->batch_size_base : max((float) $plan->planned_qty, 0.00001);
        $batches = DB::table('bc_plan_batches')->where('plan_no', $plan->plan_no)->where('source', 'wms')->orderBy('batch_no')->get();

        // Started or done keep their size; what they cover comes off the order.
        $uncovered = (float) $plan->planned_qty;
        foreach ($batches->whereIn('wms_status', ['in_progress', 'completed']) as $b) {
            $uncovered -= (float) $b->quantity_base;
        }
        $needed = $uncovered < 0.00001 ? 0 : (int) ceil(round($uncovered / $size, 6));
        $used = 0;
        foreach ($batches->where('wms_status', 'pending')->whereNull('wms_order_no') as $b) {
            if ($used < $needed) {
                $qty = round(min($size, $uncovered - $used * $size), 5);
                if (abs((float) $b->quantity_base - $qty) > 0.000001) {
                    DB::table('bc_plan_batches')->where('id', $b->id)->update(['quantity_base' => $qty, 'quantity' => round($qty / $size, 5), 'updated_at' => $now]);
                    $stats['resized']++;
                }
                $used++;
            } else {
                DB::table('bc_plan_batches')->where('id', $b->id)->update(['wms_status' => 'cancelled', 'updated_at' => $now]);
                $stats['cancelled']++;
            }
        }
        $next = (int) DB::table('bc_plan_batches')->where('plan_no', $plan->plan_no)->max('batch_no');
        while ($used < $needed) {
            $next++;
            $qty = round(min($size, $uncovered - $used * $size), 5);
            DB::table('bc_plan_batches')->insert([
                'batch_code' => mb_substr($plan->plan_no . '-W' . str_pad((string) $next, 3, '0', STR_PAD_LEFT), 0, 30),
                'plan_no' => $plan->plan_no,
                'plan_line_no' => 10000,
                'firm_planned_no' => $plan->released_from ?? null,
                'batch_no' => $next,
                'item_no' => $plan->item_no,
                'location_code' => $plan->location_code,
                'due_date' => $plan->plan_date,
                'batch_uom' => (float) $plan->batch_size_base > 0 ? 'BATCH' : $plan->uom,
                'quantity' => round($qty / $size, 5),
                'base_uom' => $plan->uom,
                'quantity_base' => $qty,
                'bc_status' => 'sent',
                'wms_status' => 'pending',
                'source' => 'wms',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $stats['created']++;
            $used++;
        }
    }
}
