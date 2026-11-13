<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * SQL route into BC (CP17 fallback, used until the Azure AD app for the API pages exists).
 *
 * WMS writes execution orders into the BC staging tables that the FARMERSCHOICE job already processes
 * ("WMSProduction Order Header" / "WMS Production Journal Line"), and close-the-day requests into
 * "WMS Plan Close Request". BC owns everything after that: posting, finishing, netting and closing.
 *
 * The same rules as the API pages apply here: a transaction id is accepted once (CP19, CP23), and an execution order
 * for a closed plan is rejected (CP4).
 */
class BcStaging
{
    private $conn;
    private $company;
    private $baseApp;
    private $extApp;
    private $columns = [];

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('bc_plan.sql');
        $this->conn = $config['connection'];
        $this->company = $config['company'];
        $this->baseApp = $config['base_app_id'];
        $this->extApp = $config['ext_app_id'];
    }

    public function db()
    {
        return DB::connection($this->conn);
    }

    public function table(string $name, bool $extension = true): string
    {
        return $this->company . '$' . $name . '$' . ($extension ? $this->extApp : $this->baseApp);
    }

    private function q(string $table): string
    {
        return '[' . str_replace(']', ']]', $table) . ']';
    }

    public function columns(string $table): array
    {
        if (!isset($this->columns[$table])) {
            $this->columns[$table] = array_map(fn($r) => $r->name, $this->db()->select(
                'SELECT name FROM sys.columns WHERE object_id = OBJECT_ID(?)', ['dbo.' . $this->q($table)]
            ));
        }
        return $this->columns[$table];
    }

    /** True once the FARMERSCHOICE build with the plan fields is deployed on the BC database. */
    public function supportsPlans(): bool
    {
        $cols = $this->columns($this->table('WMSProduction Order Header'));
        return in_array('Plan Order No_', $cols, true) && in_array('WMS Transaction ID', $cols, true);
    }

    public function supportsCloseRequests(): bool
    {
        return (bool) $this->columns($this->table('WMS Plan Close Request'));
    }

    private function requirePlans(): void
    {
        if (!$this->supportsPlans()) {
            throw new RuntimeException('BC staging tables have no plan fields yet: deploy the FARMERSCHOICE build from branch feature/mrp-wms-plan-integration first.');
        }
    }

    /** Plan state in BC: 'open', 'closed' or null when it is not a published plan. */
    public function planState(string $planNo): ?string
    {
        $po = $this->q($this->table('Production Order', false));
        $ext = $this->q($this->table('Production Order'));
        if (!in_array('WMS Plan Status', $this->columns($this->table('Production Order')), true)) {
            // Plan fields not deployed: a Released order counts as open (pilot mode, as SqlPlanSource)
            $row = $this->db()->selectOne("SELECT MAX([Status]) AS s FROM {$po} WHERE [No_] = ? AND [Status] IN (3, 4)", [$planNo]);
            return $row && $row->s !== null ? ((int) $row->s === 3 ? 'open' : 'closed') : null;
        }
        $row = $this->db()->selectOne("SELECT TOP 1 h.[Status] AS s, e.[WMS Plan Status] AS ps FROM {$po} h
            JOIN {$ext} e ON e.[Status] = h.[Status] AND e.[No_] = h.[No_]
            WHERE h.[No_] = ? AND h.[Status] IN (3, 4) ORDER BY h.[Status]", [$planNo]);
        if (!$row || (int) $row->ps === 0) {
            return null;
        }
        return ((int) $row->s === 3 && (int) $row->ps === 1) ? 'open' : 'closed';
    }

    /**
     * Stage one execution order. $header: production_order_no, transaction_id, plan_order_no, item_no, quantity, uom,
     * location_code, bin_code, user, routing, date_time (UTC), transaction_date, external_document_no.
     * $lines: line_no, item_no, quantity, uom, location_code, bin_code, entry_type ('output'|'consumption'),
     * date_time (UTC), user, batch_no, plan_component_line_no.
     * @return array{result:string,message:string} result = staged | duplicate | rejected
     */
    public function stageExecutionOrder(array $header, array $lines): array
    {
        $this->requirePlans();
        $h = $this->table('WMSProduction Order Header');
        $l = $this->table('WMS Production Journal Line');

        return $this->db()->transaction(function () use ($header, $lines, $h, $l) {
            $dup = $this->db()->selectOne('SELECT TOP 1 [Production Order No_] AS no FROM ' . $this->q($h) . ' WITH (UPDLOCK, HOLDLOCK) WHERE [WMS Transaction ID] = ?', [$header['transaction_id']]);
            if ($dup) {
                return ['result' => 'duplicate', 'message' => "Already received as {$dup->no}."];
            }
            if (!empty($header['plan_order_no'])) {
                $state = $this->planState($header['plan_order_no']);
                if ($state !== 'open') {
                    return ['result' => 'rejected', 'message' => $state === 'closed'
                        ? "Plan {$header['plan_order_no']} is closed; weighings after closure are rejected."
                        : "Plan {$header['plan_order_no']} is not a published BC plan."];
                }
            }
            if (!empty($header['plan_batch_no']) && ($msg = $this->batchProblem($header))) {
                return ['result' => 'rejected', 'message' => $msg];
            }
            if ($this->db()->selectOne('SELECT 1 AS x FROM ' . $this->q($l) . ' WHERE [Production Order No_] = ?', [$header['production_order_no']])) {
                return ['result' => 'rejected', 'message' => "Execution order {$header['production_order_no']} already has lines in BC staging under another transaction."];
            }

            $this->insert($h, [
                'Production Order No_' => $header['production_order_no'],
                'Item No_' => $header['item_no'],
                'Quantity' => $header['quantity'],
                'UOM' => $header['uom'] ?? '',
                'Location Code' => $header['location_code'] ?? '',
                'Bin Code' => $header['bin_code'] ?? '',
                'User' => mb_substr((string) ($header['user'] ?? ''), 0, 50),
                'Routing' => $header['routing'] ?? '',
                'Date Time' => $header['date_time'],
                'Transaction Date' => $header['transaction_date'],
                'External Document No_' => $header['external_document_no'] ?? '',
                'Status' => 0,
                'Plan Order No_' => $header['plan_order_no'] ?? '',
                'WMS Transaction ID' => $header['transaction_id'],
                'Plan Line No_' => (int) ($header['plan_line_no'] ?? 0),
                'Plan Batch No_' => (int) ($header['plan_batch_no'] ?? 0),
                // Written explicitly: the SQL default is 0 whatever the AL InitValue says.
                'Batch Completed' => ($header['batch_completed'] ?? true) ? 1 : 0,
            ]);
            foreach ($lines as $line) {
                $this->insert($l, [
                    'Production Order No_' => $header['production_order_no'],
                    'Line No_' => $line['line_no'],
                    'Item No_' => $line['item_no'],
                    'Quantity' => $line['quantity'],
                    'UOM' => $line['uom'] ?? '',
                    'Location Code' => $line['location_code'] ?? '',
                    'Bin Code' => $line['bin_code'] ?? '',
                    'EntryType' => $line['entry_type'] === 'output' ? 0 : 1,
                    'Date Time' => $line['date_time'],
                    'User' => mb_substr((string) ($line['user'] ?? ''), 0, 50),
                    'Status' => 0,
                    'Error' => 0,
                    'Batch No_' => $line['batch_no'] ?? '',
                    'Plan Component Line No_' => $line['plan_component_line_no'] ?? 0,
                ]);
            }
            return ['result' => 'staged', 'message' => ''];
        });
    }

    /** Same rule as the BC API page: the batch must exist on the plan and not be cancelled. */
    private function batchProblem(array $header): ?string
    {
        $t = $this->table('WMS Plan Batch');
        if (!$this->columns($t)) {
            return null; // batches not deployed yet: BC nets against the plan only
        }
        $sql = 'SELECT TOP 1 [Status] AS s, [Batch Code] AS code FROM ' . $this->q($t) . ' WHERE [Prod_ Order No_] = ? AND [Batch No_] = ?';
        $params = [$header['plan_order_no'], (int) $header['plan_batch_no']];
        if (!empty($header['plan_line_no'])) {
            $sql .= ' AND [Prod_ Order Line No_] = ?';
            $params[] = (int) $header['plan_line_no'];
        }
        $row = $this->db()->selectOne($sql, $params);
        if (!$row) {
            return "Batch {$header['plan_batch_no']} of plan {$header['plan_order_no']} does not exist in BC.";
        }
        return (int) $row->s === 4 ? "Batch {$row->code} was cancelled in BC." : null;
    }

    // Only columns that exist are written; every BC column has a SQL default for the rest.
    private function insert(string $table, array $values): void
    {
        $cols = array_flip($this->columns($table));
        $values = array_filter($values, fn($v, $k) => isset($cols[$k]), ARRAY_FILTER_USE_BOTH);
        $names = implode(', ', array_map(fn($c) => $this->q($c), array_keys($values)));
        $marks = implode(', ', array_fill(0, count($values), '?'));
        $this->db()->insert('INSERT INTO ' . $this->q($table) . " ({$names}) VALUES ({$marks})", array_values($values));
    }

    /** Status of staged execution orders by transaction id (S10 status back to WMS). */
    public function statuses(array $transactionIds): array
    {
        $this->requirePlans();
        $names = [0 => 'pending', 1 => 'processing', 2 => 'completed', 3 => 'error', 4 => 'processed', 5 => 'held'];
        $h = $this->q($this->table('WMSProduction Order Header'));
        $hasMsg = in_array('Status Message', $this->columns($this->table('WMSProduction Order Header')), true);
        $out = [];
        foreach (array_chunk(array_values(array_unique($transactionIds)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $rows = $this->db()->select("SELECT [WMS Transaction ID] AS tx, [Status] AS s, [FinishedProductionOrderNo] AS fin" . ($hasMsg ? ', [Status Message] AS msg' : ", '' AS msg") . " FROM {$h} WHERE [WMS Transaction ID] IN ({$in})", $chunk);
            foreach ($rows as $r) {
                $out[$r->tx] = ['status' => $names[(int) $r->s] ?? (string) $r->s, 'message' => $r->msg, 'finished_order_no' => $r->fin];
            }
        }
        return $out;
    }

    /** Close-the-day request (S12). BC's job queue closes the plan. Idempotent while a request is pending. */
    public function requestClose(string $planNo, ?string $user): string
    {
        if (!$this->supportsCloseRequests()) {
            throw new RuntimeException('BC has no WMS Plan Close Request table yet: deploy the FARMERSCHOICE build from branch feature/mrp-wms-plan-integration first.');
        }
        $t = $this->table('WMS Plan Close Request');
        if ($this->db()->selectOne('SELECT 1 AS x FROM ' . $this->q($t) . ' WHERE [Plan Order No_] = ? AND [Status] IN (0, 1)', [$planNo])) {
            return 'already requested';
        }
        $this->insert($t, [
            'Plan Order No_' => $planNo,
            'Requested At' => now()->utc()->format('Y-m-d H:i:s'),
            'Requested By' => mb_substr((string) $user, 0, 50),
            'Status' => 0,
            'Source' => 'WMS',
        ]);
        return 'requested';
    }
}
