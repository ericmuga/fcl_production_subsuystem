<?php

namespace App\Http\Controllers;

use App\Services\BcPlans\PlanProcess;
use App\Services\BcPlans\PlanPurge;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Delete BC orders nobody has acted on (PlanPurge): list, ask BC to delete, remove withdrawn ones from WMS. */
class PlanPurgeController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Purge orders';
        $filters = [
            'from' => $request->input('from', today()->toDateString()),
            'to' => $request->input('to'),
            'section' => $request->input('section', 'all'),
        ];
        $sections = PlanProcess::options();
        $error = null;
        $purge = app(PlanPurge::class);
        try {
            $r = $purge->refresh();
            if ($r['deleted'] || $r['kept']) {
                Toastr::info("BC deleted {$r['deleted']} and kept {$r['kept']} since the last check.", 'Purge');
            }
        } catch (\Throwable $e) {
            $error = 'Could not read BC delete status: ' . $e->getMessage();
        }
        $plans = $purge->candidates(['from' => $filters['from'], 'to' => $filters['to'], 'section' => $filters['section'] === 'all' ? null : $filters['section']]);
        return view('planned_orders.purge', compact('title', 'filters', 'sections', 'plans', 'error'));
    }

    public function request(Request $request)
    {
        $data = $request->validate(['plans' => 'required|array|min:1', 'plans.*' => 'string|max:20']);
        $results = app(PlanPurge::class)->request($data['plans'], Auth::id(), optional(Auth::user())->username);
        $gone = collect($results)->where('withdrawn', true)->count();
        $ok = collect($results)->where('ok', true)->count() - $gone;
        if ($gone) {
            Toastr::success("{$gone} order(s) were already deleted in BC and are now withdrawn; remove them from WMS below.", 'Purge');
        }
        if ($ok) {
            Toastr::success("{$ok} order(s) sent to BC to delete.", 'Purge');
        }
        foreach (collect($results)->where('ok', false)->take(5) as $no => $r) {
            Toastr::warning("{$no}: {$r['message']}", 'Purge');
        }
        return redirect()->route('planned_orders_purge', $request->only('from', 'to', 'section'));
    }

    public function forget(Request $request)
    {
        $data = $request->validate(['plans' => 'nullable|array', 'plans.*' => 'string|max:20']);
        $n = app(PlanPurge::class)->forget($data['plans'] ?? []);
        Toastr::success("{$n} withdrawn order(s) removed from WMS.", 'Purge');
        return redirect()->route('planned_orders_purge', $request->only('from', 'to', 'section'));
    }
}
