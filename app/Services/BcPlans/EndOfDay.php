<?php

namespace App\Services\BcPlans;

use Illuminate\Support\Facades\DB;

/**
 * End of day: closes the day's BC orders from WMS (integration map S12).
 * For each order: whatever was posted in WMS and not yet sent goes to BC first; then WMS asks BC to close it
 * (WMS Plan Close Request; BC's job "WMS Plan Job" closes and finishes the order, so the unproduced remainder drops out
 * of supply and the next MRP run replans it). The order shows "closing" in WMS until BC has finished it, and takes no
 * more weighings; batches nobody started are cancelled.
 * An order with weighings captured but not posted is not closed: post or delete them first.
 */
class EndOfDay
{
    public const CLOSABLE = ['open', 'released'];

    /** Orders that can be closed for this day (due that day or earlier), optionally for one section. */
    public function candidates(string $date, ?string $section = null)
    {
        $produced = DB::table('generated_production_orders')->where('line_type', 'output')->whereNotNull('plan_order_no')
            ->groupBy('plan_order_no')->select('plan_order_no', DB::raw('SUM(quantity) AS produced'));
        $batches = DB::table('bc_plan_batches')->where('wms_status', '<>', 'cancelled')->groupBy('plan_no')
            ->select('plan_no', DB::raw('COUNT(*) AS batches'), DB::raw("SUM(CASE WHEN wms_status = 'completed' THEN 1 ELSE 0 END) AS batches_done"));
        $unposted = DB::table('plan_batch_weighings')->whereNull('wms_order_no')->groupBy('plan_no')
            ->select('plan_no', DB::raw('COUNT(*) AS unposted'));
        return DB::table('bc_plan_orders AS p')
            ->leftJoinSub($produced, 'x', 'x.plan_order_no', '=', 'p.plan_no')
            ->leftJoinSub($batches, 'b', 'b.plan_no', '=', 'p.plan_no')
            ->leftJoinSub($unposted, 'w', 'w.plan_no', '=', 'p.plan_no')
            ->whereIn('p.status', array_merge(self::CLOSABLE, ['closing']))
            ->where('p.plan_date', '<=', $date)
            ->tap(fn($q) => PlanProcess::filter($q, $section))
            ->orderBy('p.status')->orderBy('p.plan_date')->orderBy('p.section')->orderBy('p.item_no')
            ->select('p.*', DB::raw('COALESCE(x.produced, 0) AS produced'), DB::raw('COALESCE(b.batches, 0) AS batches'),
                DB::raw('COALESCE(b.batches_done, 0) AS batches_done'), DB::raw('COALESCE(w.unposted, 0) AS unposted'))
            ->get();
    }

    /** @return array<string, array{ok:bool,message:string}> per order */
    public function close(array $planNos, ?int $userId, ?string $username, ?PlanSource $source = null, ?ExecutionPublisher $publisher = null): array
    {
        $source = $source ?? (app()->bound(PlanSource::class) ? app(PlanSource::class) : PlanSync::make()->source());
        $publisher = $publisher ?? app(ExecutionPublisher::class);
        $results = [];
        foreach (array_unique($planNos) as $no) {
            $plan = DB::table('bc_plan_orders')->where('plan_no', $no)->first();
            if (!$plan || !in_array($plan->status, self::CLOSABLE, true)) {
                $results[$no] = ['ok' => false, 'message' => $plan ? 'Already ' . $plan->status . '.' : 'Not found.'];
                continue;
            }
            $unposted = DB::table('plan_batch_weighings')->where('plan_no', $no)->whereNull('wms_order_no')->count();
            if ($unposted) {
                $results[$no] = ['ok' => false, 'message' => "{$unposted} weighing(s) captured but not posted: post or delete them first."];
                continue;
            }
            try {
                // Anything posted in WMS but not sent yet goes to BC before the close request.
                $waiting = DB::table('generated_production_orders')->where('plan_order_no', $no)->whereNull('bc_staged_at')
                    ->whereNotNull('transaction_id')->distinct()->pluck('production_order_no')->all();
                if ($waiting) {
                    $publisher->publishNow($waiting);
                }
                $source->close($no, $username);
                DB::transaction(function () use ($no, $userId) {
                    DB::table('bc_plan_orders')->where('plan_no', $no)->update([
                        'status' => 'closing', 'close_requested_at' => now(), 'close_requested_by' => $userId,
                        'close_message' => 'Close requested; BC closes the order on its next job run.', 'updated_at' => now(),
                    ]);
                    DB::table('bc_plan_batches')->where('plan_no', $no)->where('wms_status', 'pending')->whereNull('wms_order_no')
                        ->update(['wms_status' => 'cancelled', 'updated_at' => now()]);
                });
                $results[$no] = ['ok' => true, 'message' => 'Close requested.'];
            } catch (\Throwable $e) {
                DB::table('bc_plan_orders')->where('plan_no', $no)->update(['close_message' => mb_substr('Close failed: ' . $e->getMessage(), 0, 250), 'updated_at' => now()]);
                $results[$no] = ['ok' => false, 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    /** Read BC's answer for orders that are closing: closed once BC has finished them. @return int orders closed */
    public function refresh(?BcStaging $staging = null): int
    {
        $closing = DB::table('bc_plan_orders')->where('status', 'closing')->pluck('plan_no')->all();
        if (!$closing) {
            return 0;
        }
        $statuses = ($staging ?? new BcStaging())->closeRequestStatuses($closing);
        $closed = 0;
        foreach ($statuses as $no => $s) {
            if ($s['status'] === 'completed') {
                DB::table('bc_plan_orders')->where('plan_no', $no)->update([
                    'status' => 'closed', 'closed_at' => now(), 'close_message' => 'Closed in BC.', 'payload_hash' => null, 'updated_at' => now(),
                ]);
                $closed++;
            } else {
                $msg = $s['status'] === 'error' ? 'BC could not close it yet (retrying): ' . $s['message'] : 'BC: ' . $s['status'] . '.';
                DB::table('bc_plan_orders')->where('plan_no', $no)->update(['close_message' => mb_substr($msg, 0, 250), 'updated_at' => now()]);
            }
        }
        return $closed;
    }
}
