<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Posts one run of a batch as a WMS production order and sends it to BC straight away:
 * generated_production_orders rows (WB<batch id>, -2, -3 ... for further partial runs), the batch marked completed or
 * in progress, then ExecutionPublisher::publishNow stages it in BC's WMS production order tables, where BC's job
 * "WMS Post Plan Executions" creates the production order and posts it. If BC cannot be reached the order stays
 * queued and bc:push-executions sends it later.
 */
class BatchPoster
{
    /**
     * $lines: type (output|consumption), item_no, quantity (kg), uom, location_code, by_product.
     * @return array{order_no:string,bc:string,message:string}
     */
    public function post(object $plan, object $batch, array $lines, bool $completed, ?int $userId, ?ExecutionPublisher $publisher = null): array
    {
        $orderNo = DB::transaction(function () use ($plan, $batch, $lines, $completed, $userId) {
            $runs = DB::table('generated_production_orders')->where('batch_code', $batch->batch_code)->distinct()->count('production_order_no');
            $orderNo = 'WB' . $batch->id . ($runs ? '-' . ($runs + 1) : '');
            $now = now();
            $common = [
                'production_order_no' => $orderNo,
                'plan_order_no' => $plan->plan_no,
                'plan_line_no' => $batch->plan_line_no,
                'plan_batch_no' => $batch->batch_no,
                'batch_code' => $batch->batch_code,
                'batch_completed' => $completed ? 1 : 0,
                'transaction_id' => PlanResolver::transactionId('PLB', $orderNo),
                'external_document_no' => $batch->batch_code,
                'process' => 'Plan batch',
                'recipe' => $plan->item_no,
                'step' => 1,
                'batch_no' => $batch->batch_code,
                'user_id' => $userId,
                'transaction_date' => $now->toDateString(),
                // Sent to BC by the SQL route (publishNow / bc:push-executions), never by the legacy ProductionData export.
                'published' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $lineNo = 1000;
            $rows = [];
            foreach ($lines as $l) {
                $qty = (float) $l['quantity'];
                if ($qty <= 0) {
                    continue;
                }
                $isOutput = $l['type'] === 'output';
                $rows[] = $common + [
                    'line_no' => $isOutput ? 1000 : ($lineNo += 1000),
                    'line_type' => $isOutput ? 'output' : 'consumption',
                    'item_no' => $l['item_no'],
                    // by-products are posted as negative consumption (integration map CP7)
                    'quantity' => round(!$isOutput && !empty($l['by_product']) ? -$qty : $qty, 5),
                    'uom' => ($l['uom'] ?? null) ?: 'KG',
                    'location_code' => $l['location_code'] ?? null,
                    'net_weight' => $isOutput ? round($qty, 5) : null,
                ];
            }
            DB::table('generated_production_orders')->insert($rows);
            DB::table('bc_plan_batches')->where('id', $batch->id)->update([
                'wms_order_no' => $orderNo, 'wms_status' => $completed ? 'completed' : 'in_progress', 'updated_at' => $now,
            ]);
            return $orderNo;
        });

        // Straight to BC: no waiting for the scheduled push.
        try {
            $s = ($publisher ?? app(ExecutionPublisher::class))->publishNow([$orderNo]);
            if ($s['staged'] || $s['duplicate']) {
                return ['order_no' => $orderNo, 'bc' => 'sent', 'message' => 'Sent to BC; BC posts it within a minute.'];
            }
            if ($s['rejected']) {
                $msg = DB::table('generated_production_orders')->where('production_order_no', $orderNo)->value('bc_status_message');
                return ['order_no' => $orderNo, 'bc' => 'rejected', 'message' => 'BC rejected it: ' . $msg];
            }
            $msg = DB::table('generated_production_orders')->where('production_order_no', $orderNo)->value('bc_status_message');
            return ['order_no' => $orderNo, 'bc' => 'queued', 'message' => 'Queued for BC (' . ($msg ?: 'not sent') . '); it is sent on the next push.'];
        } catch (\Throwable $e) {
            return ['order_no' => $orderNo, 'bc' => 'queued', 'message' => 'Queued for BC (' . mb_substr($e->getMessage(), 0, 150) . '); it is sent on the next push.'];
        }
    }
}
