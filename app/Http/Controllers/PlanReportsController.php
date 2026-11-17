<?php

namespace App\Http\Controllers;

use App\Services\BcPlans\PlanReports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** MRP reports: material requirements and execution rate per item and location, for plans due in a date range. */
class PlanReportsController extends Controller
{
    public const REQUIREMENT_COLUMNS = [
        'date' => 'Due date', 'item_no' => 'Item', 'description' => 'Description', 'location_code' => 'Location', 'uom' => 'Unit',
        'firm_planned_need' => 'Firm planned need', 'released_need' => 'Released need', 'consumed' => 'Consumed in WMS',
        'remaining' => 'Remaining need', 'stock' => 'Stock (BC)', 'on_po' => 'On purchase order (BC)', 'shortage' => 'Shortage',
        'ws_supply' => 'Worksheet supply (BC)', 'ws_need' => 'Worksheet component need (BC)', 'ws_lines' => 'Worksheet lines',
    ];

    public const EXECUTION_COLUMNS = [
        'date' => 'Due date', 'item_no' => 'Item', 'description' => 'Description', 'location_code' => 'Location', 'uom' => 'Unit',
        'firm_planned' => 'Firm planned (not released)', 'planned' => 'Planned', 'posted_wms' => 'Posted in WMS',
        'posted_bc' => 'Posted in BC', 'remaining' => 'Remaining', 'rate_wms' => 'Execution % (WMS)', 'rate_bc' => 'Execution % (BC)',
        'orders' => 'Orders', 'closed' => 'Closed orders',
    ];

    public function index(Request $request)
    {
        $data = $request->validate([
            'view' => 'nullable|in:requirements,execution', 'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from',
            'location' => 'nullable|string|max:10', 'item' => 'nullable|string|max:20', 'section' => 'nullable|string|max:30',
            'by_day' => 'nullable|boolean', 'shortages' => 'nullable|boolean', 'export' => 'nullable|in:csv',
        ]);
        $view = $data['view'] ?? 'requirements';
        $from = $data['from'] ?? ($view === 'execution' ? today()->startOfWeek()->toDateString() : today()->toDateString());
        $to = $data['to'] ?? ($view === 'execution' ? today()->toDateString() : today()->addDays(6)->toDateString());
        $location = ($data['location'] ?? '') ?: null;
        $item = ($data['item'] ?? '') ?: null;
        $section = (($data['section'] ?? 'all') !== 'all') ? $data['section'] : null;
        $byDay = (bool) ($data['by_day'] ?? false);
        $shortagesOnly = (bool) ($data['shortages'] ?? false);

        $reports = new PlanReports();
        if ($view === 'execution') {
            $rows = $reports->execution($from, $to, $location, $item, $section, $byDay);
            $columns = self::EXECUTION_COLUMNS;
        } else {
            $rows = $reports->requirements($from, $to, $location, $item, $section, $byDay);
            if ($shortagesOnly) {
                $rows = array_values(array_filter($rows, fn($r) => $r['shortage'] > 0));
            }
            $columns = self::REQUIREMENT_COLUMNS;
        }
        if (!$byDay) {
            unset($columns['date']);
        }

        if (($data['export'] ?? null) === 'csv') {
            $name = ($view === 'execution' ? 'execution-rate' : 'material-requirements') . "_{$from}_{$to}.csv";
            return response(PlanReports::csv($rows, $columns), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $name . '"',
            ]);
        }

        $totals = [];
        foreach (array_keys($columns) as $k) {
            if (!in_array($k, ['date', 'item_no', 'description', 'location_code', 'uom', 'rate_wms', 'rate_bc'], true)) {
                $totals[$k] = array_sum(array_map(fn($r) => (float) ($r[$k] ?? 0), $rows));
            }
        }
        if ($view === 'execution') {
            $totals['rate_wms'] = ($totals['planned'] ?? 0) > 0 ? round($totals['posted_wms'] / $totals['planned'] * 100, 1) : null;
            $totals['rate_bc'] = ($totals['planned'] ?? 0) > 0 ? round($totals['posted_bc'] / $totals['planned'] * 100, 1) : null;
        }

        $title = $view === 'execution' ? 'Execution Rate' : 'Material Requirements';
        $sections = array_values(array_unique(array_values(config('bc_plan.sections', []))));
        $locations = DB::table('bc_plan_orders')->distinct()->orderBy('location_code')->pluck('location_code')->all();
        $bcError = $reports->bcError;
        return view('planned_orders.reports', compact('title', 'view', 'from', 'to', 'location', 'item', 'section', 'byDay',
            'shortagesOnly', 'rows', 'columns', 'totals', 'sections', 'locations', 'bcError'));
    }
}
