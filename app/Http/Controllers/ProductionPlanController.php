<?php

namespace App\Http\Controllers;

use App\Services\BcPlans\PlanSync;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Day targets from the BC MRP plan (integration map S5) and the WMS close-the-day function (S12).
 * Produced so far = output lines of WMS execution orders that carry the plan no.
 */
class ProductionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $title = 'Production Plan';
        $date = $request->input('date', today()->toDateString());
        $location = $request->input('location', 'all');
        $status = $request->input('status', 'all');

        $produced = DB::table('generated_production_orders')
            ->where('line_type', 'output')->whereNotNull('plan_order_no')
            ->groupBy('plan_order_no')
            ->select('plan_order_no', DB::raw('SUM(quantity) AS produced'), DB::raw('COUNT(DISTINCT production_order_no) AS orders'));

        $plans = DB::table('bc_plan_orders AS p')
            ->leftJoinSub($produced, 'x', 'x.plan_order_no', '=', 'p.plan_no')
            ->leftJoin('items AS i', 'i.code', '=', 'p.item_no')
            ->where('p.plan_date', $date)
            ->when($location !== 'all', fn($q) => $q->where('p.location_code', $location))
            ->when($status !== 'all', fn($q) => $q->where('p.status', $status))
            ->orderBy('p.location_code')->orderBy('p.item_no')
            ->select('p.*', DB::raw('COALESCE(i.description, p.description) AS item_description'),
                DB::raw('COALESCE(x.produced, 0) AS produced'), DB::raw('COALESCE(x.orders, 0) AS orders'))
            ->get();

        $locations = DB::table('bc_plan_orders')->where('plan_date', $date)->distinct()->orderBy('location_code')->pluck('location_code');
        $lastSync = DB::table('bc_plan_orders')->max('synced_at');

        return view('production_plan.index', compact('title', 'plans', 'date', 'location', 'status', 'locations', 'lastSync'));
    }

    public function components(string $planNo)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        abort_unless($plan, 404);
        $components = DB::table('bc_plan_order_components')->where('plan_no', $planNo)->orderBy('plan_line_no')->orderBy('line_no')->get();
        return response()->json(['plan' => $plan, 'components' => $components]);
    }

    public function sync(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        try {
            $stats = PlanSync::make()->sync(
                now()->parse($date)->subDays(config('bc_plan.days_back'))->toDateString(),
                now()->parse($date)->addDays(config('bc_plan.days_ahead'))->toDateString()
            );
            Toastr::success("Plans synced from BC: {$stats['created']} new, {$stats['updated']} updated, {$stats['withdrawn']} withdrawn.", 'Production Plan');
        } catch (\Throwable $e) {
            Log::error('BC plan sync failed: ' . $e->getMessage());
            Toastr::error('Plan sync from BC failed: ' . $e->getMessage(), 'Production Plan');
        }
        return redirect()->route('production_plan', ['date' => $date]);
    }

    // Supervisor closes the day for a plan (S12). BC finishes the plan; later weighings for it are rejected (CP4).
    public function close(Request $request, string $planNo)
    {
        $plan = DB::table('bc_plan_orders')->where('plan_no', $planNo)->first();
        abort_unless($plan, 404);
        if ($plan->status !== 'open') {
            Toastr::warning("Plan {$planNo} is already {$plan->status}.", 'Production Plan');
            return back();
        }
        try {
            PlanSync::make()->source()->close($planNo, Auth::user()->username ?? null);
            DB::table('bc_plan_orders')->where('plan_no', $planNo)->update([
                'status' => 'closed', 'closed_at' => now(), 'closed_by' => Auth::user()->username ?? null, 'payload_hash' => null, 'updated_at' => now(),
            ]);
            Toastr::success("Plan {$planNo} closed.", 'Production Plan');
        } catch (\Throwable $e) {
            Log::error("Closing BC plan {$planNo} failed: " . $e->getMessage());
            Toastr::error("Plan {$planNo} not closed: " . $e->getMessage(), 'Production Plan');
        }
        return back();
    }
}
