<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Reads plans straight from the BC tables (the existing staging + SQL route, kept as fallback in CP17).
 *
 * Published mode: the FARMERSCHOICE extension is deployed, so the Production Order extension table has
 * "WMS Plan Status"; only orders published to WMS (Open / Closed) are plans.
 * Pilot mode: the extension fields are not there yet; every Released order that BC planning created is a plan,
 * which excludes the execution orders the WMS job creates (they carry the WMS order no. in Description 2).
 */
class SqlPlanSource implements PlanSource
{
    private $conn;
    private $company;
    private $baseApp;
    private $extApp;
    private $locations;
    private $published;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('bc_plan.sql');
        $this->conn = $config['connection'];
        $this->company = $config['company'];
        $this->baseApp = $config['base_app_id'];
        $this->extApp = $config['ext_app_id'];
        $this->locations = $config['locations'] ?? [];
    }

    public function name(): string
    {
        return 'sql';
    }

    private function t(string $table, ?string $app = null): string
    {
        return '[' . $this->company . '$' . $table . '$' . ($app ?? $this->baseApp) . ']';
    }

    public function publishedMode(): bool
    {
        if ($this->published === null) {
            $name = $this->company . '$Production Order$' . $this->extApp;
            $this->published = DB::connection($this->conn)->selectOne(
                "SELECT COUNT(*) AS n FROM sys.columns WHERE object_id = OBJECT_ID(?) AND name = 'WMS Plan Status'",
                [$name]
            )->n > 0;
        }
        return $this->published;
    }

    public function fetch(string $from, string $to): array
    {
        $db = DB::connection($this->conn);
        $po = $this->t('Production Order');
        $ext = $this->t('Production Order', $this->extApp);
        $params = [$from, $to];
        $locFilter = '';
        if ($this->locations) {
            $locFilter = ' AND h.[Location Code] IN (' . implode(',', array_fill(0, count($this->locations), '?')) . ')';
            $params = array_merge($params, array_values($this->locations));
        }

        if ($this->publishedMode()) {
            // Status 3 = Released, 4 = Finished. WMS Plan Status 1 = Open, 2 = Closed.
            $headers = $db->select("
                SELECT h.[No_] AS plan_no, h.[Source No_] AS item_no, h.[Description] AS description, h.[Location Code] AS location_code,
                       CONVERT(char(10), h.[Due Date], 23) AS plan_date, h.[Status] AS bc_status, h.[\$systemModifiedAt] AS bc_modified_at,
                       e.[WMS Plan Status] AS plan_status, e.[WMS Planned Qty] AS planned_qty, e.[WMS Executed Qty] AS executed_qty,
                       e.[WMS Closed At] AS closed_at, e.[WMS Closed By] AS closed_by
                FROM {$po} h JOIN {$ext} e ON e.[Status] = h.[Status] AND e.[No_] = h.[No_]
                WHERE h.[Status] IN (3, 4) AND e.[WMS Plan Status] IN (1, 2)
                  AND h.[Due Date] BETWEEN ? AND ?{$locFilter}", $params);
        } else {
            $headers = $db->select("
                SELECT h.[No_] AS plan_no, h.[Source No_] AS item_no, h.[Description] AS description, h.[Location Code] AS location_code,
                       CONVERT(char(10), h.[Due Date], 23) AS plan_date, h.[Status] AS bc_status, h.[\$systemModifiedAt] AS bc_modified_at,
                       1 AS plan_status, NULL AS planned_qty, 0 AS executed_qty, NULL AS closed_at, NULL AS closed_by
                FROM {$po} h
                WHERE h.[Status] = 3 AND h.[Source Type] = 0 AND h.[Description 2] = ''
                  AND h.[Due Date] BETWEEN ? AND ?{$locFilter}", $params);
        }
        if (!$headers) {
            return [];
        }

        $byNo = [];
        foreach ($headers as $h) {
            $byNo[$h->plan_no] = $h;
        }
        $lines = [];
        $components = [];
        foreach (array_chunk(array_keys($byNo), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach ($db->select("
                SELECT l.[Prod_ Order No_] AS plan_no, l.[Status] AS bc_status, SUM(l.[Quantity]) AS qty, MIN(l.[Unit of Measure Code]) AS uom
                FROM {$this->t('Prod_ Order Line')} l WHERE l.[Status] IN (3, 4) AND l.[Prod_ Order No_] IN ({$in})
                GROUP BY l.[Prod_ Order No_], l.[Status]", $chunk) as $l) {
                $lines[$l->plan_no . '|' . $l->bc_status] = $l;
            }
            foreach ($db->select("
                SELECT c.[Prod_ Order No_] AS plan_no, c.[Status] AS bc_status, c.[Prod_ Order Line No_] AS plan_line_no, c.[Line No_] AS line_no,
                       c.[Item No_] AS item_no, c.[Description] AS description, c.[Unit of Measure Code] AS uom, c.[Quantity per] AS qty_per,
                       c.[Expected Quantity] AS expected_qty, c.[Remaining Quantity] AS remaining_qty, c.[Location Code] AS location_code,
                       c.[Flushing Method] AS flushing_method
                FROM {$this->t('Prod_ Order Component')} c WHERE c.[Status] IN (3, 4) AND c.[Prod_ Order No_] IN ({$in})
                ORDER BY c.[Prod_ Order No_], c.[Prod_ Order Line No_], c.[Line No_]", $chunk) as $c) {
                $components[$c->plan_no . '|' . $c->bc_status][] = $c;
            }
        }

        $flushing = ['Manual', 'Forward', 'Backward', 'Pick + Forward', 'Pick + Backward', 'Pick + Manual'];
        $plans = [];
        foreach ($byNo as $no => $h) {
            $key = $no . '|' . $h->bc_status;
            $lineQty = isset($lines[$key]) ? (float) $lines[$key]->qty : 0.0;
            $executed = (float) $h->executed_qty;
            // Published: planned qty is frozen at publish time. Pilot: what is on the lines.
            $planned = $h->planned_qty !== null ? (float) $h->planned_qty : $lineQty + $executed;
            $plans[] = [
                'plan_no' => $no,
                'item_no' => $h->item_no,
                'description' => $h->description,
                'location_code' => $h->location_code,
                'plan_date' => $h->plan_date,
                'uom' => $lines[$key]->uom ?? null,
                'planned_qty' => round($planned, 5),
                'executed_qty' => round($executed, 5),
                'remaining_qty' => round(max($planned - $executed, 0), 5),
                'status' => ((int) $h->plan_status === 2 || (int) $h->bc_status === 4) ? 'closed' : 'open',
                'bc_modified_at' => $h->bc_modified_at,
                'closed_at' => $h->closed_at && substr((string) $h->closed_at, 0, 4) !== '1753' ? $h->closed_at : null,
                'closed_by' => $h->closed_by ?: null,
                'components' => array_map(function ($c) use ($flushing) {
                    return [
                        'plan_line_no' => (int) $c->plan_line_no,
                        'line_no' => (int) $c->line_no,
                        'item_no' => $c->item_no,
                        'description' => $c->description,
                        'uom' => $c->uom,
                        'qty_per' => round((float) $c->qty_per, 5),
                        'expected_qty' => round((float) $c->expected_qty, 5),
                        'remaining_qty' => round((float) $c->remaining_qty, 5),
                        'location_code' => $c->location_code,
                        'flushing_method' => $flushing[(int) $c->flushing_method] ?? (string) $c->flushing_method,
                        'is_by_product' => (float) $c->qty_per < 0,
                    ];
                }, $components[$key] ?? []),
            ];
        }
        return $plans;
    }

    public function close(string $planNo, ?string $user = null): void
    {
        // Finishing the order is BC's job: WMS only writes a close request, the BC job queue ("WMS Plan Job") closes it.
        (new BcStaging())->requestClose($planNo, $user);
    }
}
