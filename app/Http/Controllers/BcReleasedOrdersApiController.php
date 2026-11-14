<?php

namespace App\Http\Controllers;

use App\Services\BcPlans\HttpPlanSource;
use App\Services\BcPlans\LocalBatcher;
use App\Services\BcPlans\PlanSync;
use App\Services\BcPlans\ReleasedOrderPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * POST /api/v1/bc/released-orders (Bearer VALID_BEARER_TOKEN): BC posts each released order after it is pushed
 * (codeunit 51519110, Integration Setup WMS-RELEASED-ORDERS). WMS stores the order and splits it into batch orders:
 * BC's batches when BC cut them, otherwise WMS cuts them (BATCH unit, 80 kg). Every batch order carries the BC order
 * (consolidated released order), the firm planned order it was released from and the date. Re-posting an order
 * updates it; unchanged orders change nothing.
 */
class BcReleasedOrdersApiController extends Controller
{
    public function store(Request $request)
    {
        // token_check compares against VALID_BEARER_TOKEN; an empty token would let a bare "Bearer " header through.
        if (trim((string) config('app.valid_bearer_token')) === '') {
            return response()->json(['success' => false, 'message' => 'WMS has no VALID_BEARER_TOKEN configured; set it and the same value as Auth Token in BC.'], 503);
        }
        $v =Validator::make($request->all(), ReleasedOrderPayload::rules());
        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid payload', 'errors' => $v->errors()], 422);
        }
        $sentAt = $request->input('sent_at') ? substr(str_replace('T', ' ', (string) $request->input('sent_at')), 0, 19) : now()->utc()->format('Y-m-d H:i:s');
        $plans = array_map(fn($o) => ReleasedOrderPayload::toPlan($o, $sentAt), $request->input('orders'));

        try {
            $stats = (new PlanSync(new HttpPlanSource()))->ingest($plans);
            $nos = array_column($plans, 'plan_no');
            $cut = (new LocalBatcher())->run($nos);
        } catch (\Throwable $e) {
            Log::error('BC released orders: ' . $e->getMessage(), ['orders' => array_column($plans, 'plan_no')]);
            return response()->json(['success' => false, 'message' => 'WMS could not store the orders: ' . $e->getMessage()], 500);
        }

        $orders = [];
        foreach ($nos as $no) {
            $plan = DB::table('bc_plan_orders')->where('plan_no', $no)->first(['plan_no', 'status', 'plan_date', 'released_from', 'section']);
            $orders[] = [
                'order_no' => $no,
                'firm_planned_no' => $plan->released_from,
                'date' => $plan->plan_date,
                'wms_status' => $plan->status,
                'section' => $plan->section,
                'batches' => DB::table('bc_plan_batches')->where('plan_no', $no)->where('wms_status', '<>', 'cancelled')
                    ->orderBy('plan_line_no')->orderBy('batch_no')
                    ->get(['batch_code', 'batch_no', 'quantity_base', 'source', 'wms_status'])
                    ->map(fn($b) => ['batch_code' => $b->batch_code, 'batch_no' => (int) $b->batch_no, 'quantity_kg' => (float) $b->quantity_base,
                        'cut_by' => $b->source === 'bc' ? 'BC' : 'WMS', 'wms_status' => $b->wms_status])->all(),
            ];
        }
        return response()->json([
            'success' => true,
            'received' => count($plans),
            'created' => $stats['created'],
            'updated' => $stats['updated'],
            'unchanged' => $stats['unchanged'],
            'batches_cut_in_wms' => $cut['created'],
            'orders' => $orders,
        ]);
    }
}
