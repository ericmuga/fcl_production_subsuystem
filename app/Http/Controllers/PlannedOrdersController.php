<?php

namespace App\Http\Controllers;

use App\Services\BcPlans\PlanResolver;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Planned production orders from BC (firm planned, released, open in WMS) on the WMS dashboard, filterable by item,
 * order no., planned (firm planned) order no., section, status and date; and the batch fulfilment form: one WMS
 * order per batch, the batch target shown, output and components entered in kg and sent to BC as an execution order.
 */
class PlannedOrdersController extends Controller
{
    public const STATUSES = [
        'active' => 'Active (planned, released, open)',
        'firm_planned' => 'Firm planned',
        'released' => 'Released, not pushed',
        'open' => 'Open in WMS',
        'closed' => 'Closed',
        'all' => 'All',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Default window: last Monday (today when it is Monday) to today, so the page never loads everything. */
    public static function defaultWindow(): array
    {
        return [today()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString(), today()->toDateString()];
    }

    /** Active plans (firm planned, released, open) in the default window, for the dashboard card. */
    public static function activeCount(): ?int
    {
        try {
            [$from, $to] = self::defaultWindow();
            return DB::table('bc_plan_orders')->whereIn('status', ['firm_planned', 'released', 'open'])
                ->whereBetween('plan_date', [$from, $to])->count();
        } catch (\Illuminate\Database\QueryException $e) {
            return null;
        }
    }

    /** Filters + one page of plans for the planned orders page. */
    public static function dashboardData(Request $request): array
    {
        [$defFrom, $defTo] = self::defaultWindow();
        $f = [
            'item' => trim((string) $request->input('pp_item', '')),
            'order' => trim((string) $request->input('pp_order', '')),
            'planned' => trim((string) $request->input('pp_planned', '')),
            'section' => (string) $request->input('pp_section', 'all'),
            'status' => (string) $request->input('pp_status', 'active'),
            // Pre-filtered to last Monday .. today; the dates can be changed (or cleared) on the page.
            'from' => (string) ($request->has('pp_from') ? $request->input('pp_from', '') : $defFrom),
            'to' => (string) ($request->has('pp_to') ? $request->input('pp_to', '') : $defTo),
        ];
        try {
            $batches = DB::table('bc_plan_batches')->where('wms_status', '<>', 'cancelled')->groupBy('plan_no')
                ->select('plan_no', DB::raw('COUNT(*) AS batches'),
                    DB::raw("SUM(CASE WHEN wms_status = 'completed' THEN 1 ELSE 0 END) AS batches_done"),
                    DB::raw("SUM(CASE WHEN wms_status = 'pending' THEN 1 ELSE 0 END) AS batches_pending"));
            $produced = DB::table('generated_production_orders')->where('line_type', 'output')->whereNotNull('plan_order_no')
                ->groupBy('plan_order_no')->select('plan_order_no', DB::raw('SUM(quantity) AS produced'));

            $plans = DB::table('bc_plan_orders AS p')
                ->leftJoinSub($batches, 'b', 'b.plan_no', '=', 'p.plan_no')
                ->leftJoinSub($produced, 'x', 'x.plan_order_no', '=', 'p.plan_no')
                ->when($f['item'] !== '', fn($q) => $q->where(fn($w) => $w->where('p.item_no', 'like', $f['item'] . '%')->orWhere('p.description', 'like', '%' . $f['item'] . '%')))
                ->when($f['order'] !== '', fn($q) => $q->where('p.plan_no', 'like', '%' . $f['order'] . '%'))
                ->when($f['planned'] !== '', fn($q) => $q->where(fn($w) => $w->where('p.released_from', 'like', '%' . $f['planned'] . '%')
                    ->orWhere(fn($v) => $v->where('p.status', 'firm_planned')->where('p.plan_no', 'like', '%' . $f['planned'] . '%'))))
                ->when($f['section'] !== 'all', fn($q) => $q->where('p.sections', 'like', '%' . $f['section'] . '%'))
                ->when($f['status'] === 'active', fn($q) => $q->whereIn('p.status', ['firm_planned', 'released', 'open']))
                ->when(!in_array($f['status'], ['active', 'all'], true), fn($q) => $q->where('p.status', $f['status']))
                ->when($f['from'] !== '', fn($q) => $q->where('p.plan_date', '>=', $f['from']))
                ->when($f['to'] !== '', fn($q) => $q->where('p.plan_date', '<=', $f['to']))
                ->orderBy('p.plan_date')->orderBy('p.section')->orderBy('p.item_no')
                ->select('p.*', DB::raw('COALESCE(b.batches, 0) AS batches'), DB::raw('COALESCE(b.batches_done, 0) AS batches_done'),
                    DB::raw('COALESCE(b.batches_pending, 0) AS batches_pending'), DB::raw('COALESCE(x.produced, 0) AS produced'))
                ->paginate(25, ['*'], 'pp_page')->appends($request->except('pp_page'));
            $sections = array_values(array_unique(array_values(config('bc_plan.sections', []))));
            $lastSync = DB::table('bc_sync_state')->where('key', 'plans')->value('last_run_at');
            return ['plans' => $plans, 'filters' => $f, 'sections' => $sections, 'statuses' => self::STATUSES, 'lastSync' => $lastSync, 'error' => null];
        } catch (\Illuminate\Database\QueryException $e) {
            return ['plans' => collect(), 'filters' => $f, 'sections' => [], 'statuses' => self::STATUSES, 'lastSync' => null,
                'error' => 'Planned orders are not available yet: run php artisan migrate and php artisan bc:sync-plans.'];
        }
    }

    public function index(Request $request)
    {
        $title = 'Planned Production Orders';
        $pp = self::dashboardData($request);
        return view('planned_orders.index', compact('title', 'pp'));
    }

    /** Order details for the modal: header, components and batches. */
    public function details(string $planNo)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        abort_unless($plan, 404);
        return response()->json([
            'plan' => $plan,
            'components' => DB::table('bc_plan_order_components')->where('plan_no', $planNo)->orderBy('plan_line_no')->orderBy('line_no')->get(),
            'batches' => DB::table('bc_plan_batches')->where('plan_no', $planNo)->orderBy('plan_line_no')->orderBy('batch_no')->get(),
            'fulfil_url' => $plan->status === 'open' ? route('planned_orders_fulfil', $planNo) : null,
        ]);
    }

    // ---------------- batch fulfilment ----------------

    public function fulfil(Request $request, string $planNo)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        abort_unless($plan, 404);
        $batchQuery = DB::table('bc_plan_batches')->where('plan_no', $planNo);
        $batch = $request->filled('batch')
            ? (clone $batchQuery)->where('batch_code', $request->input('batch'))->first()
            : (clone $batchQuery)->whereIn('wms_status', ['pending', 'in_progress'])->whereIn('bc_status', ['sent', 'in_progress'])
                ->orderByRaw("CASE wms_status WHEN 'in_progress' THEN 0 ELSE 1 END")->orderBy('plan_line_no')->orderBy('batch_no')->first();
        $openBatches = (clone $batchQuery)->whereIn('wms_status', ['pending', 'in_progress'])->orderBy('batch_no')->get();

        $title = 'Fulfil ' . ($batch->batch_code ?? $planNo);
        $lines = $batch ? $this->defaultLines($plan, $batch) : [];
        $done = (clone $batchQuery)->where('wms_status', 'completed')->count();
        $total = (clone $batchQuery)->where('wms_status', '<>', 'cancelled')->count();
        return view('planned_orders.fulfil', compact('title', 'plan', 'batch', 'lines', 'openBatches', 'done', 'total'));
    }

    /**
     * Autopopulated lines for one batch, in kg: the output at the batch target, each component at its share of the
     * plan for this batch (expected qty x batch / plan remaining), by-products as negative consumption.
     */
    private function defaultLines(object $plan, object $batch): array
    {
        $comps = DB::table('bc_plan_order_components')->where('plan_no', $plan->plan_no)
            ->where('plan_line_no', $batch->plan_line_no)->orderBy('line_no')->get();
        $base = (float) $plan->remaining_qty > 0 ? (float) $plan->remaining_qty : (float) $plan->planned_qty;
        $factor = $base > 0 ? (float) $batch->quantity_base / $base : 0;
        $lines = [[
            'type' => 'output', 'item_no' => $plan->item_no, 'description' => $plan->description,
            'location_code' => $batch->location_code ?: $plan->location_code, 'uom' => $batch->base_uom ?: 'KG',
            'target' => round((float) $batch->quantity_base, 3), 'quantity' => round((float) $batch->quantity_base, 3), 'by_product' => false,
        ]];
        foreach ($comps as $c) {
            $per = $factor > 0 ? (float) $c->expected_qty * $factor : (float) $c->qty_per * (float) $batch->quantity;
            $lines[] = [
                'type' => 'consumption', 'item_no' => $c->item_no, 'description' => $c->description,
                'location_code' => $c->location_code, 'uom' => $c->uom ?: 'KG',
                'target' => round(abs($per), 3), 'quantity' => round(abs($per), 3), 'by_product' => (bool) $c->is_by_product,
            ];
        }
        return $lines;
    }

    public function storeFulfilment(Request $request, string $planNo)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        abort_unless($plan, 404);
        $data = $request->validate([
            'batch_code' => 'required|string',
            'batch_completed' => 'nullable|boolean',
            'lines' => 'required|array|min:1',
            'lines.*.type' => 'required|in:output,consumption',
            'lines.*.item_no' => 'required|string|max:20',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.uom' => 'nullable|string|max:10',
            'lines.*.location_code' => 'nullable|string|max:10',
            'lines.*.by_product' => 'nullable|boolean',
        ]);
        if ($plan->status !== 'open') {
            Toastr::error("Plan {$planNo} is {$plan->status}: only plans pushed to WMS (open) can be fulfilled.", 'Planned Orders');
            return back()->withInput();
        }
        $batch = DB::table('bc_plan_batches')->where('plan_no', $planNo)->where('batch_code', $data['batch_code'])->first();
        if (!$batch || in_array($batch->wms_status, ['completed', 'cancelled'], true)) {
            Toastr::error('This batch is ' . ($batch->wms_status ?? 'unknown') . '.', 'Planned Orders');
            return back()->withInput();
        }
        $output = collect($data['lines'])->firstWhere('type', 'output');
        if (!$output || (float) $output['quantity'] <= 0) {
            Toastr::error('Enter the output weight (kg).', 'Planned Orders');
            return back()->withInput();
        }

        $completed = $request->boolean('batch_completed', true);
        $orderNo = DB::transaction(function () use ($plan, $batch, $data, $completed) {
            // One WMS order per run of the batch: WB<batch id>, with -2, -3 ... for further partial runs.
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
                'user_id' => Auth::id(),
                'transaction_date' => $now->toDateString(),
                // Sent to BC by bc:push-executions (SQL route), never by the legacy ProductionData export.
                'published' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $lineNo = 1000;
            $rows = [];
            foreach ($data['lines'] as $l) {
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
                    'uom' => $l['uom'] ?: 'KG',
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

        Toastr::success("Batch {$batch->batch_code} recorded as WMS order {$orderNo}. It reaches BC on the next bc:push-executions run.", 'Planned Orders');
        return redirect()->route('planned_orders_fulfil', $planNo);
    }
}
