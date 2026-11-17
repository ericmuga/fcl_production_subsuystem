<?php

namespace App\Console\Commands;

use App\Services\BcPlans\PlanPurge;
use Illuminate\Console\Command;

/**
 * Purge BC orders nobody has acted on (PlanPurge). Without --apply it only lists what would happen.
 *   php artisan bc:purge-plans --from=2026-11-09                     list
 *   php artisan bc:purge-plans --from=2026-11-09 --apply             ask BC to delete the unactioned ones
 *   php artisan bc:purge-plans --refresh                             read BC's answers
 *   php artisan bc:purge-plans --forget [--from=..]                  remove withdrawn, never-actioned orders from WMS
 */
class PurgeBcPlans extends Command
{
    protected $signature = 'bc:purge-plans
        {--from= : first plan date (Y-m-d)}
        {--to= : last plan date (Y-m-d)}
        {--section= : section, or Section:Step}
        {--status=* : firm_planned, released, open (default all three)}
        {--plan=* : only these order numbers}
        {--apply : ask BC to delete them (otherwise only list)}
        {--refresh : read BC\'s answers for orders being deleted}
        {--forget : remove withdrawn orders never acted on from WMS}';

    protected $description = 'Delete BC production orders nobody has acted on (BC deletes, WMS follows)';

    public function handle(): int
    {
        $purge = app(PlanPurge::class);
        $filters = ['from' => $this->option('from'), 'to' => $this->option('to'), 'section' => $this->option('section')];

        if ($this->option('refresh')) {
            $r = $purge->refresh();
            $this->info("BC deleted {$r['deleted']}, kept {$r['kept']}.");
        }
        if ($this->option('forget')) {
            $this->info($purge->forget($this->option('plan'), $filters) . ' withdrawn order(s) removed from WMS.');
        }
        if ($this->option('refresh') || $this->option('forget')) {
            return 0;
        }

        $statuses = $this->option('status') ?: PlanPurge::PURGEABLE;
        $bad = array_diff($statuses, PlanPurge::PURGEABLE);
        if ($bad) {
            $this->error('Unknown status: ' . implode(', ', $bad));
            return 1;
        }
        $plans = $purge->candidates($filters + ['statuses' => $statuses]);
        if ($this->option('plan')) {
            $plans = $plans->whereIn('plan_no', $this->option('plan'));
        }
        $free = $plans->where('actioned', false);
        $this->table(['Order', 'Date', 'Status', 'Section', 'Item', 'Planned', 'Acted on'], $plans->map(fn($p) => [
            $p->plan_no, $p->plan_date, $p->status, trim($p->section . ' ' . $p->process), $p->item_no,
            number_format((float) $p->planned_qty, 2), $p->actioned ? implode(', ', $p->activity) : '-',
        ])->all());
        $this->line(count($free) . ' order(s) never acted on, ' . (count($plans) - count($free)) . ' acted on (kept).');

        if (!$this->option('apply')) {
            $this->line('Nothing changed. Add --apply to ask BC to delete the ' . count($free) . ' order(s).');
            return 0;
        }
        if (!count($free)) {
            return 0;
        }
        $results = $purge->request($free->pluck('plan_no')->all(), null, 'wms-cli');
        $gone = collect($results)->where('withdrawn', true)->count();
        $ok = collect($results)->where('ok', true)->count() - $gone;
        foreach (collect($results)->where('ok', false) as $no => $r) {
            $this->warn("{$no}: {$r['message']}");
        }
        if ($gone) {
            $this->info("{$gone} order(s) already deleted in BC: withdrawn. Run with --forget to remove them from WMS.");
        }
        $this->info("{$ok} delete request(s) sent to BC; BC's job deletes them. Run with --refresh to read the answers.");
        return 0;
    }
}
