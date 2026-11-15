<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * MRP reports in WMS, per item and location (and per due date when $byDay), for plans due in a date range.
 *
 * Execution rate: output planned on the plans (released to WMS) against output posted, both in WMS (batch orders
 * written by the weighing station) and in BC (executed quantity synced back). Firm planned output is shown apart.
 *
 * Material requirements: component demand of the plans (firm planned, released, open, closing), what WMS has consumed,
 * what remains, and from BC: stock and open purchase orders at the location, plus the latest planning / requisition
 * worksheet proposals. BC figures are read live over the bc240 connection; if BC cannot be reached the WMS figures
 * still show and $bcError says why.
 */
class PlanReports
{
    public const EXECUTION_STATUSES = ['released', 'open', 'closing', 'closed'];
    public const REQUIREMENT_STATUSES = ['firm_planned', 'released', 'open', 'closing'];

    public ?string $bcError = null;

    /** @return array<int, array> rows */
    public function execution(string $from, string $to, ?string $location = null, ?string $item = null, ?string $section = null, bool $byDay = false): array
    {
        $plans = $this->plans($from, $to, $location, $item, $section)
            ->whereIn('status', array_merge(self::EXECUTION_STATUSES, ['firm_planned']))
            ->get(['plan_no', 'item_no', 'description', 'location_code', 'plan_date', 'uom', 'planned_qty', 'executed_qty', 'status']);
        $posted = $this->postedOutput($plans->pluck('plan_no')->all());

        $rows = [];
        foreach ($plans as $p) {
            $key = $this->key($p->item_no, $p->location_code, $byDay ? substr($p->plan_date, 0, 10) : null);
            $r = &$rows[$key];
            $r = $r ?? [
                'date' => $byDay ? substr($p->plan_date, 0, 10) : null, 'item_no' => $p->item_no, 'description' => $p->description,
                'location_code' => $p->location_code, 'uom' => $p->uom, 'firm_planned' => 0.0, 'planned' => 0.0,
                'posted_wms' => 0.0, 'posted_bc' => 0.0, 'orders' => 0, 'closed' => 0,
            ];
            if ($p->status === 'firm_planned') {
                $r['firm_planned'] += (float) $p->planned_qty;
            } else {
                $r['planned'] += (float) $p->planned_qty;
                $r['posted_wms'] += (float) ($posted[$p->plan_no] ?? 0);
                $r['posted_bc'] += (float) $p->executed_qty;
                $r['orders']++;
                $r['closed'] += $p->status === 'closed' ? 1 : 0;
            }
            unset($r);
        }
        foreach ($rows as &$r) {
            $r['remaining'] = max($r['planned'] - $r['posted_wms'], 0);
            $r['rate_wms'] = $r['planned'] > 0 ? round($r['posted_wms'] / $r['planned'] * 100, 1) : null;
            $r['rate_bc'] = $r['planned'] > 0 ? round($r['posted_bc'] / $r['planned'] * 100, 1) : null;
        }
        unset($r);
        return $this->sorted($rows);
    }

    /** @return array<int, array> rows */
    public function requirements(string $from, string $to, ?string $location = null, ?string $item = null, ?string $section = null, bool $byDay = false): array
    {
        $plans = $this->plans($from, $to, null, null, $section)->whereIn('status', self::REQUIREMENT_STATUSES)
            ->get(['plan_no', 'plan_date', 'status'])->keyBy('plan_no');
        $comps = $plans->isEmpty() ? collect() : DB::table('bc_plan_order_components')
            ->whereIn('plan_no', $plans->keys()->all())->where('is_by_product', 0)
            ->when($location, fn($q) => $q->where('location_code', $location))
            ->when($item, fn($q) => $q->where('item_no', 'like', str_replace('*', '%', $item)))
            ->get(['plan_no', 'item_no', 'description', 'uom', 'location_code', 'expected_qty']);
        $consumed = $this->consumed($plans->keys()->all());

        $rows = [];
        foreach ($comps as $c) {
            $plan = $plans[$c->plan_no];
            $day = $byDay ? substr($plan->plan_date, 0, 10) : null;
            $r = &$rows[$this->key($c->item_no, (string) $c->location_code, $day)];
            $r = $r ?? $this->emptyRequirement($c->item_no, $c->description, (string) $c->location_code, $c->uom, $day);
            $need = (float) $c->expected_qty;
            $used = $plan->status === 'firm_planned' ? 0.0 : min((float) ($consumed[$c->plan_no][$c->item_no] ?? 0), $need);
            // consumption per plan and item is shared over its component lines: count it once
            if ($used > 0) {
                $consumed[$c->plan_no][$c->item_no] -= $used;
            }
            if ($plan->status === 'firm_planned') {
                $r['firm_planned_need'] += $need;
            } else {
                $r['released_need'] += $need;
            }
            $r['consumed'] += $used;
            $r['remaining'] += $need - $used;
            unset($r);
        }

        $this->addBcFigures($rows, $from, $to, $location, $item, $byDay);
        if ($byDay) {
            $this->runningShortage($rows);
        } else {
            foreach ($rows as &$r) {
                $r['shortage'] = max($r['remaining'] - ($r['stock'] ?? 0) - ($r['on_po'] ?? 0), 0);
            }
            unset($r);
        }
        return $this->sorted($rows);
    }

    /** CSV text of report rows with the given columns (key => header). */
    public static function csv(array $rows, array $columns): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, array_values($columns));
        foreach ($rows as $r) {
            fputcsv($out, array_map(fn($k) => $r[$k] ?? '', array_keys($columns)));
        }
        rewind($out);
        return stream_get_contents($out);
    }

    private function plans(string $from, string $to, ?string $location, ?string $item, ?string $section)
    {
        return DB::table('bc_plan_orders')
            ->whereBetween('plan_date', [$from, $to])
            ->when($location, fn($q) => $q->where('location_code', $location))
            ->when($item, fn($q) => $q->where('item_no', 'like', str_replace('*', '%', $item)))
            ->when($section, fn($q) => $q->where('sections', 'like', '%' . $section . '%'));
    }

    /** Output posted in WMS per plan (batch orders, all runs). */
    private function postedOutput(array $planNos): array
    {
        $out = [];
        foreach (array_chunk($planNos, 1000) as $chunk) {
            $out += DB::table('generated_production_orders')->whereIn('plan_order_no', $chunk)->where('line_type', 'output')
                ->groupBy('plan_order_no')->select('plan_order_no', DB::raw('SUM(quantity) AS qty'))->pluck('qty', 'plan_order_no')->all();
        }
        return $out;
    }

    /** Components consumed in WMS per plan and item (by-products, posted negative, left out). */
    private function consumed(array $planNos): array
    {
        $out = [];
        foreach (array_chunk($planNos, 1000) as $chunk) {
            DB::table('generated_production_orders')->whereIn('plan_order_no', $chunk)->where('line_type', 'consumption')
                ->where('quantity', '>', 0)->groupBy('plan_order_no', 'item_no')
                ->select('plan_order_no', 'item_no', DB::raw('SUM(quantity) AS qty'))->get()
                ->each(function ($r) use (&$out) { $out[$r->plan_order_no][$r->item_no] = (float) $r->qty; });
        }
        return $out;
    }

    /** Stock, open purchase orders and worksheet proposals from BC. Worksheet-only rows are added. */
    private function addBcFigures(array &$rows, string $from, string $to, ?string $location, ?string $item, bool $byDay): void
    {
        try {
            $cfg = config('bc_plan.sql');
            $db = DB::connection($cfg['connection']);
            $t = fn(string $table) => '[' . $cfg['company'] . '$' . $table . '$' . $cfg['base_app_id'] . ']';

            // worksheet proposals (supply) and their component need, per item, location and due date
            $ws = $db->table(DB::raw($t('Requisition Line')))->where('Type', 2)->whereBetween('Due Date', [$from, $to])
                ->when($location, fn($q) => $q->where('Location Code', $location))
                ->when($item, fn($q) => $q->where('No_', 'like', str_replace('*', '%', $item)))
                ->groupBy('No_', 'Location Code', 'Due Date')
                ->select(DB::raw('[No_] AS item_no, [Location Code] AS loc, CONVERT(date, [Due Date]) AS due, SUM([Quantity (Base)]) AS qty, COUNT(*) AS n'))->get();
            foreach ($ws as $w) {
                $r = &$rows[$this->key($w->item_no, $w->loc, $byDay ? substr($w->due, 0, 10) : null)];
                $r = $r ?? $this->emptyRequirement($w->item_no, null, $w->loc, null, $byDay ? substr($w->due, 0, 10) : null);
                $r['ws_supply'] += (float) $w->qty;
                $r['ws_lines'] += (int) $w->n;
                unset($r);
            }
            $pc = $db->table(DB::raw($t('Planning Component')))->whereBetween('Due Date', [$from, $to])
                ->when($location, fn($q) => $q->where('Location Code', $location))
                ->when($item, fn($q) => $q->where('Item No_', 'like', str_replace('*', '%', $item)))
                ->groupBy('Item No_', 'Location Code', 'Due Date')
                ->select(DB::raw('[Item No_] AS item_no, [Location Code] AS loc, CONVERT(date, [Due Date]) AS due, SUM([Expected Quantity (Base)]) AS qty'))->get();
            foreach ($pc as $w) {
                $r = &$rows[$this->key($w->item_no, $w->loc, $byDay ? substr($w->due, 0, 10) : null)];
                $r = $r ?? $this->emptyRequirement($w->item_no, null, $w->loc, null, $byDay ? substr($w->due, 0, 10) : null);
                $r['ws_need'] += (float) $w->qty;
                unset($r);
            }

            // stock and open purchase orders per item and location
            $items = array_values(array_unique(array_column($rows, 'item_no')));
            $stock = $onPo = $info = [];
            foreach (array_chunk($items, 500) as $chunk) {
                $db->table(DB::raw($t('Item Ledger Entry')))->whereIn('Item No_', $chunk)
                    ->groupBy('Item No_', 'Location Code')
                    ->select(DB::raw('[Item No_] AS item_no, [Location Code] AS loc, SUM(Quantity) AS qty'))->get()
                    ->each(function ($s) use (&$stock) { $stock[$s->item_no . '|' . $s->loc] = (float) $s->qty; });
                $db->table(DB::raw($t('Purchase Line')))->where('Document Type', 1)->where('Type', 2)->whereIn('No_', $chunk)
                    ->where('Expected Receipt Date', '<=', $to)
                    ->groupBy('No_', 'Location Code')
                    ->select(DB::raw('[No_] AS item_no, [Location Code] AS loc, SUM([Outstanding Qty_ (Base)]) AS qty'))->get()
                    ->each(function ($s) use (&$onPo) { $onPo[$s->item_no . '|' . $s->loc] = (float) $s->qty; });
                $db->table(DB::raw($t('Item')))->whereIn('No_', $chunk)
                    ->select(DB::raw('[No_] AS item_no, Description AS description, [Base Unit of Measure] AS uom'))->get()
                    ->each(function ($s) use (&$info) { $info[$s->item_no] = $s; });
            }
            foreach ($rows as &$r) {
                $k = $r['item_no'] . '|' . $r['location_code'];
                $r['stock'] = $stock[$k] ?? 0.0;
                $r['on_po'] = $onPo[$k] ?? 0.0;
                if (isset($info[$r['item_no']])) {
                    $r['description'] = $r['description'] ?: $info[$r['item_no']]->description;
                    $r['uom'] = $r['uom'] ?: $info[$r['item_no']]->uom;
                }
            }
            unset($r);
        } catch (\Throwable $e) {
            $this->bcError = 'BC figures (stock, purchase orders, worksheet) could not be read: ' . mb_substr($e->getMessage(), 0, 200);
        }
    }

    /** Per day, stock and purchase orders are used by the earliest days of the same item and location first. */
    private function runningShortage(array &$rows): void
    {
        $keys = array_keys($rows);
        usort($keys, fn($a, $b) => [$rows[$a]['item_no'], $rows[$a]['location_code'], $rows[$a]['date']]
            <=> [$rows[$b]['item_no'], $rows[$b]['location_code'], $rows[$b]['date']]);
        $cover = [];
        foreach ($keys as $k) {
            $r = &$rows[$k];
            $g = $r['item_no'] . '|' . $r['location_code'];
            $cover[$g] = $cover[$g] ?? (($r['stock'] ?? 0) + ($r['on_po'] ?? 0));
            $use = min($cover[$g], $r['remaining']);
            $r['shortage'] = $r['remaining'] - $use;
            $cover[$g] -= $use;
            unset($r);
        }
    }

    private function emptyRequirement(string $itemNo, ?string $description, string $location, ?string $uom, ?string $date): array
    {
        return [
            'date' => $date, 'item_no' => $itemNo, 'description' => $description, 'location_code' => $location, 'uom' => $uom,
            'firm_planned_need' => 0.0, 'released_need' => 0.0, 'consumed' => 0.0, 'remaining' => 0.0,
            'stock' => null, 'on_po' => null, 'shortage' => 0.0, 'ws_supply' => 0.0, 'ws_need' => 0.0, 'ws_lines' => 0,
        ];
    }

    private function key(string $item, string $location, ?string $date): string
    {
        return $item . '|' . $location . '|' . ($date ?? '');
    }

    private function sorted(array $rows): array
    {
        $rows = array_values($rows);
        usort($rows, fn($a, $b) => [$a['date'] ?? '', $a['item_no'], $a['location_code']] <=> [$b['date'] ?? '', $b['item_no'], $b['location_code']]);
        return $rows;
    }
}
