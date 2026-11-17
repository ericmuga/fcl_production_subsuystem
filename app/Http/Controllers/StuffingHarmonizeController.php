<?php

namespace App\Http\Controllers;

use App\Services\Stuffing\PlanMatcher;
use App\Services\Stuffing\ProductionDataWriter;
use App\Services\Stuffing\StuffingHarmonizer;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Harmonize stuffing batches: line up the generated orders with the dispatch weight,
 * choose how, preview, then save and (in review mode) release to ProductionData.
 */
class StuffingHarmonizeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, StuffingHarmonizer $harmonizer, ProductionDataWriter $writer)
    {
        $from = $request->input('from', today()->subDays(2)->toDateString());
        $to = $request->input('to', today()->toDateString());
        $state = in_array($request->input('state'), ['open', 'pending', 'harmonized', 'released', 'all']) ? $request->input('state') : 'open';
        $mode = $request->input('mode', StuffingHarmonizer::defaultMode());
        $mode = isset(StuffingHarmonizer::MODES[$mode]) ? $mode : StuffingHarmonizer::defaultMode();

        $batches = $harmonizer->batches($from, $to, $request->input('q'), $state);

        $preview = null;
        $orders = collect();
        if ($request->filled('batch') && $request->filled('item')) {
            $preview = $harmonizer->plan($request->input('batch'), $request->input('item'), $mode);
            // Plan per order (each order matched on its own output), with the plans it could be matched to.
            $matcher = app(PlanMatcher::class);
            $orders = $matcher->outputs($preview['lines']->pluck('production_order_no')->unique()->all())
                ->map(function ($o) use ($matcher) {
                    $o->candidates = $matcher->candidates($o, true);
                    $o->sent = $o->bc_staged_at || $o->production_data_at || $o->released_at || (int) $o->published === 1;
                    return $o;
                })->keyBy('production_order_no');
        }

        return view('sausage.harmonize', [
            'batches' => $batches,
            'preview' => $preview,
            'orders' => $orders,
            'plannedRoute' => config('production_orders.planned_route'),
            'from' => $from,
            'to' => $to,
            'state' => $state,
            'mode' => $mode,
            'modes' => StuffingHarmonizer::MODES,
            'holds' => $writer->holdsForHarmonization(),
            'writes' => $writer->enabled(),
            'title' => 'Harmonize stuffing batches',
        ]);
    }

    /** Match a batch's orders to plans automatically, or set / clear one order's plan by hand. */
    public function matchPlan(Request $request, PlanMatcher $matcher)
    {
        $data = $request->validate([
            'batch' => 'required|string|max:50',
            'item' => 'required|string|max:30',
            'order' => 'nullable|string|max:100',
            'plan' => 'nullable|string|max:20',      // plan no. | '' (no plan) | 'auto'
        ]);

        try {
            if (empty($data['order'])) {
                $orderNos = \Illuminate\Support\Facades\DB::table('generated_production_orders')
                    ->where('batch_no', $data['batch'])->where('packed_item', $data['item'])->distinct()->pluck('production_order_no')->all();
                $r = $matcher->autoMatch($orderNos);
                Toastr::success("{$r['matched']} order(s) matched, {$r['unmatched']} without a plan, {$r['kept']} kept.", 'Plans');
            } elseif (($data['plan'] ?? '') === 'auto') {
                $matcher->reset($data['order']);
                Toastr::success("{$data['order']} is matched automatically again.", 'Plans');
            } else {
                $matcher->setManual($data['order'], ($data['plan'] ?? '') !== '' ? $data['plan'] : null);
                Toastr::success(($data['plan'] ?? '') !== '' ? "{$data['order']} matched to {$data['plan']}." : "{$data['order']} has no plan.", 'Plans');
            }
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
        }

        return redirect()->to(route('stuffing_harmonize', $request->only('from', 'to', 'state', 'q', 'mode') + [
            'batch' => $data['batch'], 'item' => $data['item'],
        ]) . '#plans');
    }

    public function apply(Request $request, StuffingHarmonizer $harmonizer)
    {
        $data = $request->validate([
            'batch' => 'required|string|max:50',
            'item' => 'required|string|max:30',
            'mode' => 'required|in:' . implode(',', array_keys(StuffingHarmonizer::MODES)),
            'release' => 'nullable|boolean',
        ]);

        try {
            $r = $harmonizer->apply($data['batch'], $data['item'], $data['mode'], (bool) ($data['release'] ?? false), Auth::user()->username ?? 'WMS');
            foreach ($r['errors'] as $e) {
                Toastr::warning($e, 'Check');
            }
            Toastr::success("Batch {$data['batch']} / {$data['item']}: {$r['changed']} line(s) changed{$r['written']}.", 'Harmonized');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
        }

        return redirect()->route('stuffing_harmonize', $request->only('from', 'to', 'state', 'q') + [
            'batch' => $data['batch'], 'item' => $data['item'], 'mode' => $data['mode'],
        ]);
    }
}
