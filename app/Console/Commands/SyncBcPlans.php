<?php

namespace App\Console\Commands;

use App\Services\BcPlans\PlanSync;
use Illuminate\Console\Command;

class SyncBcPlans extends Command
{
    protected $signature = 'bc:sync-plans
        {--from= : first plan date (Y-m-d), default today - BC_PLAN_DAYS_BACK}
        {--to= : last plan date (Y-m-d), default today + BC_PLAN_DAYS_AHEAD}
        {--source= : sql | api (default BC_PLAN_SOURCE)}
        {--dry-run : show what would change without writing}';

    protected $description = 'Pull BC MRP daily plans (day targets per item + location) into WMS';

    public function handle()
    {
        $from = $this->option('from') ?: now()->subDays(config('bc_plan.days_back'))->toDateString();
        $to = $this->option('to') ?: now()->addDays(config('bc_plan.days_ahead'))->toDateString();
        $sync = PlanSync::make($this->option('source'));
        $source = $sync->source();
        $mode = method_exists($source, 'publishedMode') ? ($source->publishedMode() ? 'published plans' : 'pilot mode: all BC-planned released orders') : 'published plans';
        $this->info("Syncing BC plans {$from} .. {$to} from {$source->name()} ({$mode})" . ($this->option('dry-run') ? ' [dry run]' : ''));
        $stats = $sync->sync($from, $to, (bool) $this->option('dry-run'));
        $this->table(array_keys($stats), [array_values($stats)]);
        return 0;
    }
}
