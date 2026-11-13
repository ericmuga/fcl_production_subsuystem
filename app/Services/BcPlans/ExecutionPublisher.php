<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * Pushes WMS execution orders to BC over the SQL route (integration map S8-S10) and reads their status back.
 * Source: generated_production_orders (stuffing today; other stations as they are moved, CP27).
 * An order goes once (bc_staged_at); a replay after an outage is safe because BC staging accepts a transaction id once.
 */
class ExecutionPublisher
{
    private $staging;

    public function __construct(?BcStaging $staging = null)
    {
        $this->staging = $staging ?? new BcStaging();
    }

    /** @return array{candidates:int,staged:int,duplicate:int,rejected:int,errors:int,statuses:int} */
    public function run(int $limit = 500, bool $dryRun = false): array
    {
        $stats = ['candidates' => 0, 'staged' => 0, 'duplicate' => 0, 'rejected' => 0, 'errors' => 0, 'statuses' => 0];
        $orders = DB::table('generated_production_orders')
            ->whereNull('bc_staged_at')->whereNotNull('transaction_id')
            ->when(!config('bc_plan.push_unplanned'), fn($q) => $q->whereNotNull('plan_order_no'))
            ->select('production_order_no')->distinct()->orderBy('production_order_no')->limit($limit)
            ->pluck('production_order_no');
        $stats['candidates'] = $orders->count();

        $offset = (int) config('bc_plan.utc_offset_hours', 3);
        foreach ($orders as $orderNo) {
            $rows = DB::table('generated_production_orders AS g')->leftJoin('users AS u', 'u.id', '=', 'g.user_id')
                ->where('g.production_order_no', $orderNo)->orderBy('g.line_no')->select('g.*', 'u.username')->get();
            $out = $rows->firstWhere('line_type', 'output');
            if (!$out) {
                $this->mark($orderNo, 'error', 'No output line', $dryRun);
                $stats['errors']++;
                continue;
            }
            $utc = fn($t) => now()->parse($t)->subHours($offset)->format('Y-m-d H:i:s');
            $header = [
                'production_order_no' => $orderNo,
                'transaction_id' => $out->transaction_id,
                'plan_order_no' => $out->plan_order_no,
                'plan_line_no' => $out->plan_line_no ?? null,
                'plan_batch_no' => $out->plan_batch_no ?? null,
                'batch_code' => $out->batch_code ?? null,
                'batch_completed' => isset($out->batch_completed) ? (bool) $out->batch_completed : true,
                'item_no' => $out->item_no,
                'quantity' => (float) $out->quantity,
                'uom' => $out->uom,
                'location_code' => $out->location_code,
                'bin_code' => $out->bin_code,
                'user' => $out->username,
                'routing' => $out->routing,
                'date_time' => $utc($out->created_at),
                'transaction_date' => $out->transaction_date,
                'external_document_no' => $out->external_document_no,
            ];
            $lines = $rows->map(fn($r) => [
                'line_no' => (int) $r->line_no,
                'item_no' => $r->item_no,
                'quantity' => (float) $r->quantity,
                'uom' => $r->uom,
                'location_code' => $r->location_code,
                'bin_code' => $r->bin_code,
                'entry_type' => $r->line_type,
                'date_time' => $utc($r->created_at),
                'user' => $r->username,
                'batch_no' => $r->batch_no,
            ])->all();
            if ($dryRun) {
                $stats['staged']++;
                continue;
            }
            try {
                $res = $this->staging->stageExecutionOrder($header, $lines);
                $stats[$res['result']]++;
                if ($res['result'] !== 'rejected' && $header['batch_completed']) {
                    PlanResolver::completeBatch($header['batch_code']);
                }
                // staged, duplicate (already in BC) and rejected are all settled: none is sent again
                $this->mark($orderNo, $res['result'] === 'rejected' ? 'rejected' : 'pending', $res['message'], false, true);
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->mark($orderNo, 'error', mb_substr($e->getMessage(), 0, 250), false, false);
            }
        }

        if (!$dryRun) {
            $stats['statuses'] = $this->refreshStatuses();
        }
        return $stats;
    }

    private function mark(string $orderNo, string $status, string $message, bool $dryRun, bool $staged = false): void
    {
        if ($dryRun) {
            return;
        }
        DB::table('generated_production_orders')->where('production_order_no', $orderNo)->update(array_filter([
            'bc_status' => $status,
            'bc_status_message' => mb_substr($message, 0, 250),
            'bc_staged_at' => $staged ? now() : null,
        ], fn($v) => $v !== null) + ['updated_at' => now()]);
    }

    /** Pull BC's status for staged orders that are not finished yet. */
    public function refreshStatuses(): int
    {
        $open = DB::table('generated_production_orders')->whereNotNull('bc_staged_at')
            ->whereNotIn('bc_status', ['completed', 'processed', 'rejected'])
            ->select('production_order_no', 'transaction_id')->distinct()->get();
        if ($open->isEmpty()) {
            return 0;
        }
        $statuses = $this->staging->statuses($open->pluck('transaction_id')->filter()->all());
        $n = 0;
        foreach ($open as $o) {
            if (!isset($statuses[$o->transaction_id])) {
                continue;
            }
            $s = $statuses[$o->transaction_id];
            $n += DB::table('generated_production_orders')->where('production_order_no', $o->production_order_no)
                ->update(['bc_status' => $s['status'], 'bc_status_message' => mb_substr((string) $s['message'], 0, 250), 'updated_at' => now()]) ? 1 : 0;
        }
        return $n;
    }
}
