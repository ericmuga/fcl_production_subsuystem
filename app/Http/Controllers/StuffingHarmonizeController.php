<?php

namespace App\Http\Controllers;

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
        if ($request->filled('batch') && $request->filled('item')) {
            $preview = $harmonizer->plan($request->input('batch'), $request->input('item'), $mode);
        }

        return view('sausage.harmonize', [
            'batches' => $batches,
            'preview' => $preview,
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
