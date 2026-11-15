<?php

namespace App\Http\Controllers;

use App\Services\BcPlans\BatchPoster;
use App\Services\BcPlans\EndOfDay;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Weighing station for BC planned orders, and end of day.
 * Pick an order (one batch at a time) -> "Weigh item" opens a modal to search the item being weighed -> read the scale,
 * the vessel tare (per section, section_vessels) comes off: net = gross - tare x vessels -> each weighing is kept;
 * "Post batch" sums them per item into one WMS production order and sends it to BC straight away.
 */
class PlanWeighingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ---------------- station ----------------

    public function station(Request $request)
    {
        $title = 'Weigh Planned Orders';
        $date = $request->input('date', today()->toDateString());
        $section = $request->input('section', session('weigh_section', 'all'));
        session(['weigh_section' => $section]);
        $search = trim((string) $request->input('q', ''));
        $sections = array_values(array_unique(array_values(config('bc_plan.sections', []))));

        $done = DB::table('bc_plan_batches')->where('wms_status', '<>', 'cancelled')->groupBy('plan_no')
            ->select('plan_no', DB::raw('COUNT(*) AS batches'), DB::raw("SUM(CASE WHEN wms_status = 'completed' THEN 1 ELSE 0 END) AS batches_done"));
        $produced = DB::table('generated_production_orders')->where('line_type', 'output')->whereNotNull('plan_order_no')
            ->groupBy('plan_order_no')->select('plan_order_no', DB::raw('SUM(quantity) AS produced'));
        // Open orders due on the day, and earlier ones nobody closed yet.
        $orders = DB::table('bc_plan_orders AS p')
            ->leftJoinSub($done, 'b', 'b.plan_no', '=', 'p.plan_no')
            ->leftJoinSub($produced, 'x', 'x.plan_order_no', '=', 'p.plan_no')
            ->where('p.status', 'open')->where('p.plan_date', '<=', $date)
            ->when($section !== 'all', fn($q) => $q->where('p.sections', 'like', '%' . $section . '%'))
            ->when($search !== '', fn($q) => $q->where(fn($w) => $w->where('p.plan_no', 'like', '%' . $search . '%')
                ->orWhere('p.item_no', 'like', $search . '%')->orWhere('p.description', 'like', '%' . $search . '%')
                ->orWhere('p.released_from', 'like', '%' . $search . '%')))
            ->orderByDesc('p.plan_date')->orderBy('p.section')->orderBy('p.item_no')->limit(200)
            ->select('p.*', DB::raw('COALESCE(b.batches, 0) AS batches'), DB::raw('COALESCE(b.batches_done, 0) AS batches_done'),
                DB::raw('COALESCE(x.produced, 0) AS produced'))
            ->get();

        $plan = $batch = null;
        $batches = $lines = $weighings = $vessels = $scales = [];
        if ($request->filled('plan')) {
            $plan = DB::table('bc_plan_orders')->where('plan_no', $request->input('plan'))->first();
        }
        if ($plan) {
            $batches = DB::table('bc_plan_batches')->where('plan_no', $plan->plan_no)->whereIn('wms_status', ['pending', 'in_progress'])
                ->orderByRaw("CASE wms_status WHEN 'in_progress' THEN 0 ELSE 1 END")->orderBy('plan_line_no')->orderBy('batch_no')->get();
            $batch = $request->filled('batch') ? $batches->firstWhere('batch_code', $request->input('batch')) : $batches->first();
            if ($batch) {
                $weighings = DB::table('plan_batch_weighings AS w')->leftJoin('users AS u', 'u.id', '=', 'w.user_id')
                    ->where('w.batch_code', $batch->batch_code)->whereNull('w.wms_order_no')->orderByDesc('w.id')
                    ->select('w.*', 'u.username')->get();
                $lines = $this->linesWithWeighed($plan, $batch, $weighings);
            }
            $vessels = self::vesselsFor($plan->section);
            $scales = self::scalesFor($plan->section);
        }
        $manual = (bool) config('bc_plan.weigh_manual');
        $canEditVessels = self::canEditVessels();
        return view('planned_orders.weigh', compact('title', 'date', 'section', 'sections', 'search', 'orders', 'plan', 'batch', 'batches',
            'lines', 'weighings', 'vessels', 'scales', 'manual', 'canEditVessels'));
    }

    /** Batch target lines (output at the batch size, components scaled) with what was weighed so far. */
    private function linesWithWeighed(object $plan, object $batch, $weighings): array
    {
        $lines = PlannedOrdersController::batchLines($plan, $batch);
        $byItem = [];
        foreach ($weighings as $w) {
            $k = $w->line_type . '|' . $w->item_no;
            $byItem[$k] = ['net' => ($byItem[$k]['net'] ?? 0) + (float) $w->net_kg, 'count' => ($byItem[$k]['count'] ?? 0) + 1];
        }
        foreach ($lines as &$l) {
            $k = $l['type'] . '|' . $l['item_no'];
            $l['weighed'] = round($byItem[$k]['net'] ?? 0, 3);
            $l['count'] = $byItem[$k]['count'] ?? 0;
            unset($byItem[$k]);
        }
        unset($l);
        // Items weighed that are not on the order (picked from the item list).
        foreach ($byItem as $k => $v) {
            [$type, $item] = explode('|', $k, 2);
            $w = $weighings->first(fn($x) => $x->item_no === $item && $x->line_type === $type);
            $lines[] = ['type' => $type, 'item_no' => $item, 'description' => $w->description ?? '', 'location_code' => $w->location_code ?? $plan->location_code,
                'uom' => 'KG', 'target' => null, 'quantity' => 0, 'by_product' => (bool) ($w->by_product ?? false),
                'weighed' => round($v['net'], 3), 'count' => $v['count'], 'extra' => true];
        }
        return $lines;
    }

    /** Items for the search modal: the order's own items first, then the item list. */
    public function items(Request $request, string $planNo)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        abort_unless($plan, 404);
        $q = mb_strtolower(trim((string) $request->input('q', '')));
        $batch = DB::table('bc_plan_batches')->where('plan_no', $planNo)->whereIn('wms_status', ['pending', 'in_progress'])->orderBy('batch_no')->first();
        $own = $batch ? PlannedOrdersController::batchLines($plan, $batch) : [[
            'type' => 'output', 'item_no' => $plan->item_no, 'description' => $plan->description, 'location_code' => $plan->location_code,
            'uom' => 'KG', 'target' => null, 'quantity' => 0, 'by_product' => false,
        ]];
        $match = fn($no, $desc) => $q === '' || str_contains(mb_strtolower($no), $q) || str_contains(mb_strtolower((string) $desc), $q);
        $results = [];
        foreach ($own as $l) {
            if ($match($l['item_no'], $l['description'])) {
                $results[] = ['item_no' => $l['item_no'], 'description' => $l['description'], 'type' => $l['type'], 'by_product' => (bool) $l['by_product'],
                    'location_code' => $l['location_code'] ?: $plan->location_code, 'target' => $l['target'], 'on_order' => true];
            }
        }
        if (mb_strlen($q) >= 2) {
            $seen = array_flip(array_column($results, 'item_no'));
            foreach (DB::table('items')->where(fn($w) => $w->where('blocked', 0)->orWhereNull('blocked'))
                ->where(fn($w) => $w->where('code', 'like', $q . '%')->orWhere('description', 'like', '%' . $q . '%'))
                ->orderBy('code')->limit(25)->get(['code', 'description']) as $i) {
                if (!isset($seen[$i->code])) {
                    $results[] = ['item_no' => $i->code, 'description' => $i->description, 'type' => 'consumption', 'by_product' => false,
                        'location_code' => $plan->location_code, 'target' => null, 'on_order' => false];
                }
            }
        }
        return response()->json(['items' => $results]);
    }

    public function storeWeighing(Request $request, string $planNo, string $batchCode)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        $batch = DB::table('bc_plan_batches')->where('plan_no', $planNo)->where('batch_code', $batchCode)->first();
        abort_unless($plan && $batch, 404);
        $data = $request->validate([
            'item_no' => 'required|string|max:20',
            'description' => 'nullable|string|max:100',
            'line_type' => 'required|in:output,consumption',
            'by_product' => 'nullable|boolean',
            'location_code' => 'nullable|string|max:10',
            'gross_kg' => 'required|numeric|gt:0|max:100000',
            'vessel_id' => 'nullable|integer',
            'vessel_count' => 'nullable|integer|min:0|max:500',
            'scale' => 'nullable|string|max:50',
            'manual' => 'nullable|boolean',
        ]);
        if ($plan->status !== 'open') {
            return response()->json(['ok' => false, 'message' => "Order {$planNo} is " . str_replace('_', ' ', $plan->status) . ': no more weighings.'], 422);
        }
        if (!in_array($batch->wms_status, ['pending', 'in_progress'], true)) {
            return response()->json(['ok' => false, 'message' => "Batch {$batchCode} is {$batch->wms_status}."], 422);
        }
        $manual = $request->boolean('manual');
        if ($manual && !config('bc_plan.weigh_manual')) {
            return response()->json(['ok' => false, 'message' => 'Typed weights are switched off: read the scale.'], 422);
        }
        // The tare comes from the vessel set-up, not from the browser.
        $vessel = !empty($data['vessel_id']) ? DB::table('section_vessels')->where('id', $data['vessel_id'])->where('active', 1)->first() : null;
        $count = $vessel ? (int) ($data['vessel_count'] ?? 1) : 0;
        $tare = $vessel ? round((float) $vessel->tare_kg * $count, 3) : 0.0;
        $net = round((float) $data['gross_kg'] - $tare, 3);
        if ($net <= 0) {
            return response()->json(['ok' => false, 'message' => "Net weight is {$net} kg: the gross weight must be more than the vessels ({$tare} kg)."], 422);
        }
        $id = DB::table('plan_batch_weighings')->insertGetId([
            'batch_code' => $batch->batch_code, 'plan_no' => $planNo, 'item_no' => $data['item_no'],
            'description' => mb_substr((string) ($data['description'] ?? ''), 0, 100), 'line_type' => $data['line_type'],
            'by_product' => $request->boolean('by_product'), 'uom' => 'KG', 'location_code' => $data['location_code'] ?? $plan->location_code,
            'scale' => $data['scale'] ?? null, 'manual' => $manual, 'gross_kg' => round((float) $data['gross_kg'], 3),
            'vessel_id' => $vessel->id ?? null, 'vessel_name' => $vessel->name ?? null, 'vessel_tare_kg' => $vessel->tare_kg ?? 0,
            'vessel_count' => $count, 'tare_kg' => $tare, 'net_kg' => $net, 'user_id' => Auth::id(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('bc_plan_batches')->where('id', $batch->id)->where('wms_status', 'pending')->update(['wms_status' => 'in_progress', 'updated_at' => now()]);
        return response()->json(['ok' => true, 'id' => $id, 'net_kg' => $net, 'tare_kg' => $tare,
            'message' => "{$data['item_no']}: {$net} kg net recorded."]);
    }

    public function deleteWeighing(int $id)
    {
        $w = DB::table('plan_batch_weighings')->where('id', $id)->first();
        abort_unless($w, 404);
        if ($w->wms_order_no) {
            return response()->json(['ok' => false, 'message' => 'Already posted.'], 422);
        }
        DB::table('plan_batch_weighings')->where('id', $id)->delete();
        // Back to pending when nothing is left on a batch that was never posted.
        if (!DB::table('plan_batch_weighings')->where('batch_code', $w->batch_code)->exists()
            && !DB::table('generated_production_orders')->where('batch_code', $w->batch_code)->exists()) {
            DB::table('bc_plan_batches')->where('batch_code', $w->batch_code)->where('wms_status', 'in_progress')->update(['wms_status' => 'pending', 'updated_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }

    /** Sum the batch's weighings per item into one WMS production order and send it to BC now. */
    public function postBatch(Request $request, string $planNo, string $batchCode)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        $batch = DB::table('bc_plan_batches')->where('plan_no', $planNo)->where('batch_code', $batchCode)->first();
        abort_unless($plan && $batch, 404);
        $back = redirect()->route('planned_orders_weigh', ['plan' => $planNo, 'batch' => $batchCode]);
        if ($plan->status !== 'open') {
            Toastr::error("Order {$planNo} is " . str_replace('_', ' ', $plan->status) . '.', 'Weighing');
            return $back;
        }
        $weighings = DB::table('plan_batch_weighings')->where('batch_code', $batchCode)->whereNull('wms_order_no')->get();
        if (!$weighings->where('line_type', 'output')->sum('net_kg')) {
            Toastr::error('Weigh the output item before posting the batch.', 'Weighing');
            return $back;
        }
        $lines = [];
        foreach ($weighings->groupBy(fn($w) => $w->line_type . '|' . $w->item_no . '|' . (int) $w->by_product) as $group) {
            $f = $group->first();
            $lines[] = ['type' => $f->line_type, 'item_no' => $f->item_no, 'quantity' => round($group->sum('net_kg'), 3), 'uom' => 'KG',
                'location_code' => $f->location_code, 'by_product' => (bool) $f->by_product];
        }
        usort($lines, fn($a, $b) => ($a['type'] === 'output' ? 0 : 1) <=> ($b['type'] === 'output' ? 0 : 1));
        $completed = $request->boolean('batch_completed', true);
        $res = (new BatchPoster())->post($plan, $batch, $lines, $completed, Auth::id());
        DB::table('plan_batch_weighings')->whereIn('id', $weighings->pluck('id'))->update(['wms_order_no' => $res['order_no'], 'updated_at' => now()]);

        $msg = "Batch {$batchCode} posted as WMS order {$res['order_no']}. {$res['message']}";
        $res['bc'] === 'rejected' ? Toastr::warning($msg, 'Weighing') : Toastr::success($msg, 'Weighing');
        $next = DB::table('bc_plan_batches')->where('plan_no', $planNo)->whereIn('wms_status', ['pending', 'in_progress'])->orderBy('batch_no')->value('batch_code');
        return redirect()->route('planned_orders_weigh', array_filter(['plan' => $planNo, 'batch' => $next]));
    }

    // ---------------- vessels (tare per section) ----------------

    public static function vesselsFor(?string $section)
    {
        return DB::table('section_vessels')->where('active', 1)
            ->where(fn($q) => $q->where('section', (string) $section)->orWhere('section', 'All'))
            ->orderByDesc('is_default')->orderBy('name')->get();
    }

    /**
     * Scales of the section (scale_configs); read through readScale(). An order with no section, or a section with no
     * scale set up, offers every scale (labelled with its section) rather than none.
     */
    public static function scalesFor(?string $section): array
    {
        $map = config('bc_plan.section_scales', []);
        $key = $map[$section] ?? ($section ? mb_strtolower($section) : null);
        $rows = $key ? DB::table('scale_configs')->where('section', $key)->orderBy('scale')->get(['scale', 'comport', 'ip_address', 'section']) : collect();
        $all = $rows->isEmpty();
        if ($all) {
            $rows = DB::table('scale_configs')->orderBy('section')->orderBy('scale')->get(['scale', 'comport', 'ip_address', 'section']);
        }
        return $rows->map(fn($s) => [
            'scale' => $s->scale, 'comport' => $s->comport, 'ip_address' => $s->ip_address,
            'label' => $all ? $s->scale . ' (' . $s->section . ')' : $s->scale,
        ])->all();
    }

    /**
     * GET /planned-orders/scale-reading?plan=..&scale=..: reads the scale through the WMS server, like the other weighing
     * screens (Helpers::get_scale_read): the scale service on the weighing PC answers at
     * http://<scale IP, else the PC's IP><GET_WEIGHT_ENDPOINT>/<comport>. The browser never calls the scale itself.
     */
    public function readScale(Request $request)
    {
        $data = $request->validate(['plan' => 'required|string|max:20', 'scale' => 'required|string|max:50']);
        $section = DB::table('bc_plan_orders')->where('plan_no', $data['plan'])->value('section');
        $scale = collect(self::scalesFor($section))->firstWhere('scale', $data['scale']);
        if (!$scale) {
            return response()->json(['success' => false, 'message' => 'Scale ' . $data['scale'] . ' is not set up for ' . ($section ?: 'this section') . '.'], 422);
        }
        $ip = trim((string) $scale['ip_address']);
        if ($ip === '' || strtolower($ip) === 'null') {
            $ip = $request->getClientIp();
            $ip = $ip === '::1' ? '127.0.0.1' : $ip;
        }
        $endpoint = config('app.get_weight_endpoint') ?: ':3000/api/get-scale-reading';
        $url = 'http://' . $ip . $endpoint . '/' . $scale['comport'];
        try {
            $body = \Illuminate\Support\Facades\Http::timeout(5)->get($url)->body();
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'No response from the scale service at ' . $ip . '.'], 502);
        }
        // The scale service answers {"success":..,"response":<weight>}, sometimes JSON-encoded twice.
        $json = json_decode($body, true);
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        if (!is_array($json) || empty($json['success']) || !is_numeric($json['response'] ?? null)) {
            $msg = is_array($json) && isset($json['response']) ? (string) $json['response'] : 'unexpected answer';
            return response()->json(['success' => false, 'message' => 'The scale did not return a weight: ' . mb_substr($msg, 0, 120)], 502);
        }
        return response()->json(['success' => true, 'weight' => round((float) $json['response'], 3)]);
    }

    public static function canEditVessels(): bool
    {
        return in_array((string) optional(Auth::user())->role, config('bc_plan.vessel_admin_roles', ['admin']), true);
    }

    public function vessels()
    {
        $title = 'Vessels (tare per section)';
        $vessels = DB::table('section_vessels AS v')->leftJoin('users AS u', 'u.id', '=', 'v.updated_by')
            ->orderBy('v.section')->orderByDesc('v.is_default')->orderBy('v.name')->select('v.*', 'u.username')->get();
        $sections = array_merge(['All'], array_values(array_unique(array_values(config('bc_plan.sections', [])))));
        $canEdit = self::canEditVessels();
        return view('planned_orders.vessels', compact('title', 'vessels', 'sections', 'canEdit'));
    }

    public function saveVessel(Request $request)
    {
        abort_unless(self::canEditVessels(), 403);
        $data = $request->validate([
            'id' => 'nullable|integer',
            'section' => 'required|string|max:30',
            'name' => 'required|string|max:50',
            'tare_kg' => 'required|numeric|min:0|max:1000',
            'is_default' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);
        $row = ['section' => $data['section'], 'name' => $data['name'], 'tare_kg' => round((float) $data['tare_kg'], 3),
            'is_default' => $request->boolean('is_default'), 'active' => $request->boolean('active', true),
            'updated_by' => Auth::id(), 'updated_at' => now()];
        DB::transaction(function () use ($data, $row) {
            if ($row['is_default']) {
                DB::table('section_vessels')->where('section', $row['section'])->update(['is_default' => 0]);
            }
            if (!empty($data['id'])) {
                DB::table('section_vessels')->where('id', $data['id'])->update($row);
            } else {
                DB::table('section_vessels')->insert($row + ['created_at' => now()]);
            }
        });
        Toastr::success("Vessel {$data['name']} ({$data['section']}) saved: {$row['tare_kg']} kg.", 'Vessels');
        return redirect()->route('planned_orders_vessels');
    }

    // ---------------- end of day ----------------

    public function endOfDay(Request $request)
    {
        $title = 'End of Day';
        $date = $request->input('date', today()->toDateString());
        $section = $request->input('section', 'all');
        $sections = array_values(array_unique(array_values(config('bc_plan.sections', []))));
        $error = null;
        try {
            if ((new EndOfDay())->refresh()) {
                Toastr::info('BC has closed orders since the last check.', 'End of Day');
            }
        } catch (\Throwable $e) {
            $error = 'Could not read BC close status: ' . $e->getMessage();
        }
        $orders = (new EndOfDay())->candidates($date, $section === 'all' ? null : $section);
        return view('planned_orders.end_of_day', compact('title', 'date', 'section', 'sections', 'orders', 'error'));
    }

    public function closeDay(Request $request)
    {
        $data = $request->validate(['plans' => 'required|array|min:1', 'plans.*' => 'string|max:20', 'date' => 'nullable|date', 'section' => 'nullable|string']);
        $results = (new EndOfDay())->close($data['plans'], Auth::id(), optional(Auth::user())->username);
        $ok = collect($results)->where('ok', true)->count();
        $failed = collect($results)->where('ok', false);
        if ($ok) {
            Toastr::success("{$ok} order(s) sent to BC to close.", 'End of Day');
        }
        foreach ($failed->take(5) as $no => $r) {
            Toastr::warning("{$no}: {$r['message']}", 'End of Day');
        }
        return redirect()->route('planned_orders_end_of_day', array_filter(['date' => $data['date'] ?? null, 'section' => $data['section'] ?? null]));
    }
}
