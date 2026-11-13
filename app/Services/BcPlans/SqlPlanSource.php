<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Reads plans straight from the BC tables (SQL route, CP17 fallback until the API app registration exists).
 *
 * Which orders are plans:
 *   every Released order BC planning made (Source Type Item, Description 2 blank: this leaves out the execution orders
 *   the WMS job creates, which carry the WMS order no. in Description 2), plus, once the FARMERSCHOICE build is
 *   deployed, every order pushed to WMS (WMS Plan Status Open/Closed) including finished ones.
 * Status: 'released' = released in BC, not pushed to WMS yet; 'open' = pushed (WMS works its batches);
 *         'closed' = closed by WMS or finished in BC.
 * Quantities are in the item's base unit (kg), since orders may be in BATCH.
 *
 * fetch()        : plans due in a date window (full sync of the window).
 * fetchChanged() : plans whose order, lines, components or batches changed since a watermark (inserts and updates),
 *                  read from BC's $systemModifiedAt.
 */
class SqlPlanSource implements PlanSource
{
    private $conn;
    private $company;
    private $baseApp;
    private $extApp;
    private $locations;
    private $published;
    private $cols = [];

    private const BATCH_STATUS = [1 => 'sent', 2 => 'in_progress', 3 => 'completed', 4 => 'cancelled'];

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

    private function db()
    {
        return DB::connection($this->conn);
    }

    private function raw(string $table, ?string $app = null): string
    {
        return $this->company . '$' . $table . '$' . ($app ?? $this->baseApp);
    }

    private function t(string $table, ?string $app = null): string
    {
        return '[' . $this->raw($table, $app) . ']';
    }

    private function columns(string $rawTable): array
    {
        if (!isset($this->cols[$rawTable])) {
            $this->cols[$rawTable] = array_map(fn($r) => $r->name, $this->db()->select(
                'SELECT name FROM sys.columns WHERE object_id = OBJECT_ID(?)', ['dbo.[' . $rawTable . ']']
            ));
        }
        return $this->cols[$rawTable];
    }

    public function publishedMode(): bool
    {
        if ($this->published === null) {
            $this->published = in_array('WMS Plan Status', $this->columns($this->raw('Production Order', $this->extApp)), true);
        }
        return $this->published;
    }

    public function supportsBatches(): bool
    {
        return (bool) $this->columns($this->raw('WMS Plan Batch', $this->extApp));
    }

    public function fetch(string $from, string $to): array
    {
        return $this->load('h.[Due Date] BETWEEN ? AND ?', [$from, $to]);
    }

    /**
     * Plans changed since $sinceUtc (null = all current plans). Returns ['plans' => [...], 'watermark' => utc string]:
     * the watermark is BC's clock at the start of the read, so nothing committed after it is skipped next time.
     */
    public function fetchChanged(?string $sinceUtc): array
    {
        $watermark = $this->db()->selectOne('SELECT CONVERT(varchar(23), SYSUTCDATETIME(), 121) AS now')->now;
        // Plans due before the history cutoff are not read (old released orders without a due date stay out).
        $cutoff = now()->subDays((int) config('bc_plan.history_days', 30))->toDateString();
        if ($sinceUtc === null) {
            return ['plans' => $this->load('h.[Due Date] >= ?', [$cutoff]), 'watermark' => $watermark];
        }
        $parts = [
            "SELECT [No_] AS no FROM {$this->t('Production Order')} WHERE [Status] IN (2, 3, 4) AND [\$systemModifiedAt] > ?",
            "SELECT [Prod_ Order No_] FROM {$this->t('Prod_ Order Line')} WHERE [Status] IN (2, 3, 4) AND [\$systemModifiedAt] > ?",
            "SELECT [Prod_ Order No_] FROM {$this->t('Prod_ Order Component')} WHERE [Status] IN (2, 3, 4) AND [\$systemModifiedAt] > ?",
        ];
        if ($this->publishedMode()) {
            $parts[] = "SELECT [No_] FROM {$this->t('Production Order', $this->extApp)} WHERE [Status] IN (2, 3, 4) AND [\$systemModifiedAt] > ?";
        }
        if ($this->supportsBatches()) {
            $parts[] = "SELECT [Prod_ Order No_] FROM {$this->t('WMS Plan Batch', $this->extApp)} WHERE [\$systemModifiedAt] > ?";
        }
        $nos = array_map(fn($r) => $r->no, $this->db()->select(
            'SELECT DISTINCT no FROM (' . implode(' UNION ', $parts) . ') x', array_fill(0, count($parts), $sinceUtc)
        ));
        $plans = [];
        foreach (array_chunk($nos, 500) as $chunk) {
            $plans = array_merge($plans, $this->load('h.[Due Date] >= ? AND h.[No_] IN (' . implode(',', array_fill(0, count($chunk), '?')) . ')', array_merge([$cutoff], $chunk)));
        }
        return ['plans' => $plans, 'watermark' => $watermark];
    }

    /** Of the given plan nos, which are still Released in BC and which are Finished (missing = deleted). */
    public function bcStates(array $planNos): array
    {
        $out = [];
        foreach (array_chunk(array_values(array_unique($planNos)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach ($this->db()->select("SELECT [No_] AS no, MIN([Status]) AS s FROM {$this->t('Production Order')} WHERE [Status] IN (2, 3, 4) AND [No_] IN ({$in}) GROUP BY [No_]", $chunk) as $r) {
                $out[$r->no] = [2 => 'firm_planned', 3 => 'released'][(int) $r->s] ?? 'finished';
            }
        }
        return $out;
    }

    private function load(string $where, array $params): array
    {
        $db = $this->db();
        $po = $this->t('Production Order');
        $ext = $this->t('Production Order', $this->extApp);
        $locFilter = '';
        if ($this->locations) {
            $locFilter = ' AND h.[Location Code] IN (' . implode(',', array_fill(0, count($this->locations), '?')) . ')';
            $params = array_merge($params, array_values($this->locations));
        }
        // Firm Planned (2) and Released (3) orders BC planning made.
        $bcPlanned = "h.[Status] IN (2, 3) AND h.[Source Type] = 0 AND h.[Description 2] = ''";

        if ($this->publishedMode()) {
            $releasedFrom = in_array('WMS Source Firm Planned No_', $this->columns($this->raw('Production Order', $this->extApp)), true)
                ? 'e.[WMS Source Firm Planned No_]' : "''";
            // Status 3 = Released, 4 = Finished. WMS Plan Status 0 = not pushed, 1 = Open, 2 = Closed.
            $headers = $db->select("
                SELECT h.[No_] AS plan_no, h.[Source No_] AS item_no, h.[Description] AS description, h.[Location Code] AS location_code,
                       CONVERT(char(10), h.[Due Date], 23) AS plan_date, h.[Status] AS bc_status, h.[\$systemModifiedAt] AS bc_modified_at,
                       ISNULL(e.[WMS Plan Status], 0) AS plan_status, e.[WMS Planned Qty] AS planned_qty, ISNULL(e.[WMS Executed Qty], 0) AS executed_qty,
                       e.[WMS Closed At] AS closed_at, e.[WMS Closed By] AS closed_by, {$releasedFrom} AS released_from
                FROM {$po} h LEFT JOIN {$ext} e ON e.[Status] = h.[Status] AND e.[No_] = h.[No_]
                WHERE (({$bcPlanned}) OR (h.[Status] IN (3, 4) AND ISNULL(e.[WMS Plan Status], 0) IN (1, 2)))
                  AND {$where}{$locFilter}", $params);
        } else {
            $headers = $db->select("
                SELECT h.[No_] AS plan_no, h.[Source No_] AS item_no, h.[Description] AS description, h.[Location Code] AS location_code,
                       CONVERT(char(10), h.[Due Date], 23) AS plan_date, h.[Status] AS bc_status, h.[\$systemModifiedAt] AS bc_modified_at,
                       1 AS plan_status, NULL AS planned_qty, 0 AS executed_qty, NULL AS closed_at, NULL AS closed_by, '' AS released_from
                FROM {$po} h
                WHERE {$bcPlanned} AND {$where}{$locFilter}", $params);
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
        $batches = [];
        $routingLocs = [];
        $withBatches = $this->supportsBatches();
        foreach (array_chunk(array_keys($byNo), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach ($db->select("
                SELECT l.[Prod_ Order No_] AS plan_no, l.[Status] AS bc_status, SUM(l.[Quantity (Base)]) AS qty_base,
                       MIN(i.[Base Unit of Measure]) AS base_uom, MIN(l.[Unit of Measure Code]) AS line_uom
                FROM {$this->t('Prod_ Order Line')} l LEFT JOIN {$this->t('Item')} i ON i.[No_] = l.[Item No_]
                WHERE l.[Status] IN (2, 3, 4) AND l.[Prod_ Order No_] IN ({$in})
                GROUP BY l.[Prod_ Order No_], l.[Status]", $chunk) as $l) {
                $lines[$l->plan_no . '|' . $l->bc_status] = $l;
            }
            foreach ($db->select("
                SELECT r.[Prod_ Order No_] AS plan_no, r.[Status] AS bc_status, w.[Location Code] AS location_code
                FROM {$this->t('Prod_ Order Routing Line')} r JOIN {$this->t('Work Center')} w ON w.[No_] = r.[Work Center No_]
                WHERE r.[Status] IN (2, 3, 4) AND r.[Prod_ Order No_] IN ({$in})
                ORDER BY r.[Prod_ Order No_], r.[Routing Reference No_], r.[Operation No_]", $chunk) as $r) {
                $routingLocs[$r->plan_no . '|' . $r->bc_status][] = $r->location_code;
            }
            foreach ($db->select("
                SELECT c.[Prod_ Order No_] AS plan_no, c.[Status] AS bc_status, c.[Prod_ Order Line No_] AS plan_line_no, c.[Line No_] AS line_no,
                       c.[Item No_] AS item_no, c.[Description] AS description, c.[Unit of Measure Code] AS uom, c.[Quantity per] AS qty_per,
                       c.[Expected Quantity] AS expected_qty, c.[Remaining Quantity] AS remaining_qty, c.[Location Code] AS location_code,
                       c.[Flushing Method] AS flushing_method
                FROM {$this->t('Prod_ Order Component')} c WHERE c.[Status] IN (2, 3, 4) AND c.[Prod_ Order No_] IN ({$in})
                ORDER BY c.[Prod_ Order No_], c.[Prod_ Order Line No_], c.[Line No_]", $chunk) as $c) {
                $components[$c->plan_no . '|' . $c->bc_status][] = $c;
            }
            if ($withBatches) {
                // Status 0 = cut but not pushed: WMS only sees pushed batches.
                foreach ($db->select("
                    SELECT b.[Prod_ Order No_] AS plan_no, b.[Prod_ Order Line No_] AS plan_line_no, b.[Batch No_] AS batch_no, b.[Batch Code] AS batch_code,
                           b.[Item No_] AS item_no, b.[Variant Code] AS variant_code, b.[Location Code] AS location_code,
                           CONVERT(char(10), b.[Due Date], 23) AS due_date, b.[Unit of Measure Code] AS batch_uom, b.[Quantity] AS quantity,
                           b.[Base Unit of Measure] AS base_uom, b.[Quantity (Base)] AS quantity_base, b.[Executed Qty_ (Base)] AS executed_base,
                           b.[Status] AS status, b.[Pushed At] AS pushed_at, b.[Completed At] AS completed_at
                    FROM {$this->t('WMS Plan Batch', $this->extApp)} b
                    WHERE b.[Status] <> 0 AND b.[Prod_ Order No_] IN ({$in})
                    ORDER BY b.[Prod_ Order No_], b.[Prod_ Order Line No_], b.[Batch No_]", $chunk) as $b) {
                    $batches[$b->plan_no][] = $b;
                }
            }
        }

        // Batch size per item: its BATCH unit in base units (kg), used when WMS cuts the batches itself.
        $batchSize = [];
        $items = array_values(array_unique(array_map(fn($h) => $h->item_no, $headers)));
        foreach (array_chunk($items, 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach ($db->select("SELECT [Item No_] AS item_no, [Qty_ per Unit of Measure] AS qty FROM {$this->t('Item Unit of Measure')}
                WHERE [Code] = 'BATCH' AND [Item No_] IN ({$in})", $chunk) as $u) {
                $batchSize[$u->item_no] = (float) $u->qty;
            }
        }

        $flushing = ['Manual', 'Forward', 'Backward', 'Pick + Forward', 'Pick + Backward', 'Pick + Manual'];
        $bcDate = fn($v) => $v && substr((string) $v, 0, 4) !== '1753' ? (string) $v : null;
        $plans = [];
        foreach ($byNo as $no => $h) {
            $key = $no . '|' . $h->bc_status;
            $lineQty = isset($lines[$key]) ? (float) $lines[$key]->qty_base : 0.0;
            $executed = (float) $h->executed_qty;
            $ps = (int) $h->plan_status;
            // Pushed: planned qty is frozen at push time. Not pushed yet: what is on the lines.
            $planned = ($this->publishedMode() && $ps > 0 && $h->planned_qty !== null) ? (float) $h->planned_qty : $lineQty + $executed;
            if ((int) $h->bc_status === 2) {
                $status = 'firm_planned';
            } elseif ($ps === 2 || (int) $h->bc_status === 4) {
                $status = 'closed';
            } elseif ($ps === 1) {
                $status = 'open';
            } else {
                $status = 'released';
            }
            $planBatches = array_map(fn($b) => [
                'batch_code' => $b->batch_code,
                'plan_line_no' => (int) $b->plan_line_no,
                'batch_no' => (int) $b->batch_no,
                'item_no' => $b->item_no,
                'variant_code' => $b->variant_code ?: null,
                'location_code' => $b->location_code,
                'due_date' => $b->due_date,
                'batch_uom' => $b->batch_uom,
                'quantity' => round((float) $b->quantity, 5),
                'base_uom' => $b->base_uom,
                'quantity_base' => round((float) $b->quantity_base, 5),
                'bc_executed_qty_base' => round((float) $b->executed_base, 5),
                'bc_status' => self::BATCH_STATUS[(int) $b->status] ?? 'sent',
                'pushed_at' => $bcDate($b->pushed_at),
                'bc_completed_at' => $bcDate($b->completed_at),
            ], $batches[$no] ?? []);
            [$section, $sections] = self::sectionsFor($routingLocs[$key] ?? [], $h->location_code);
            $plans[] = [
                'plan_no' => $no,
                'section' => $section,
                'sections' => $sections,
                'item_no' => $h->item_no,
                'description' => $h->description,
                'location_code' => $h->location_code,
                'plan_date' => $h->plan_date,
                'uom' => $lines[$key]->base_uom ?? ($lines[$key]->line_uom ?? null),
                'batch_uom' => $planBatches[0]['batch_uom'] ?? (isset($batchSize[$h->item_no]) ? 'BATCH' : null),
                'batch_size_base' => $batchSize[$h->item_no] ?? null,
                'released_from' => $h->released_from ?: null,
                'planned_qty' => round($planned, 5),
                'executed_qty' => round($executed, 5),
                'remaining_qty' => round(max($planned - $executed, 0), 5),
                'status' => $status,
                'bc_modified_at' => $h->bc_modified_at,
                'closed_at' => $bcDate($h->closed_at),
                'closed_by' => $h->closed_by ?: null,
                'batches' => $planBatches,
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

    /** WMS section of a BC location code: longest matching prefix in config bc_plan.sections. */
    public static function sectionOf(?string $locationCode): ?string
    {
        $best = null;
        $len = 0;
        foreach (config('bc_plan.sections', []) as $prefix => $section) {
            $prefix = (string) $prefix;
            if ($locationCode !== null && strlen($prefix) > $len && strncmp($locationCode, $prefix, strlen($prefix)) === 0) {
                [$best, $len] = [$section, strlen($prefix)];
            }
        }
        return $best;
    }

    /** [main section (last operation), all sections '|'-separated] from the routing's work centre locations. */
    public static function sectionsFor(array $routingLocations, ?string $headerLocation): array
    {
        $all = array_values(array_unique(array_filter(array_map([self::class, 'sectionOf'], $routingLocations))));
        if (!$all) {
            $s = self::sectionOf($headerLocation);
            return [$s, $s];
        }
        $main = self::sectionOf(end($routingLocations)) ?? end($all);
        return [$main, implode('|', $all)];
    }

    public function close(string $planNo, ?string $user = null): void
    {
        // Finishing the order is BC's job: WMS only writes a close request, the BC job queue ("WMS Plan Job") closes it.
        (new BcStaging())->requestClose($planNo, $user);
    }
}
