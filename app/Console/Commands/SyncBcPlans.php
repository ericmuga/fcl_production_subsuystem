<?php

namespace App\Console\Commands;

use App\Services\BcPlans\PlanSync;
use Illuminate\Console\Command;

class SyncBcPlans extends Command
{
    protected $signature = 'bc:sync-plans
        {--from= : first plan date (Y-m-d): sync a date window instead of the changes since the last run}
        {--to= : last plan date (Y-m-d), with --from}
        {--full : incremental mode, but read every current plan (first run or repair)}
        {--source= : sql | api (default BC_PLAN_SOURCE)}
        {--dry-run : show what would change without writing}';

    protected $description = 'Pull BC released production orders (plans) and their WMS batches into WMS: inserts and updates since the last run';

    public function handle()
    {
        $sync = PlanSync::make($this->option('source'));
        $source = $sync->source();
        $dry = (bool) $this->option('dry-run');
        $mode = method_exists($source, 'publishedMode')
            ? ($source->publishedMode() ? 'released + pushed orders' . ($source->supportsBatches() ? ', with batches' : '') : 'pilot mode: BC build not deployed, released orders only')
            : 'API';

        $windowed = $this->option('from') || $this->option('to') || !method_exists($source, 'fetchChanged');
        if ($windowed) {
            $from = $this->option('from') ?: now()->subDays(config('bc_plan.days_back'))->toDateString();
            $to = $this->option('to') ?: now()->addDays(config('bc_plan.days_ahead'))->toDateString();
            $this->info("Syncing BC plans due {$from} .. {$to} from {$source->name()} ({$mode})" . ($dry ? ' [dry run]' : ''));
            $stats = $sync->sync($from, $to, $dry);
        } else {
            $this->info("Syncing BC plan changes from {$source->name()} ({$mode})" . ($this->option('full') ? ' [full]' : '') . ($dry ? ' [dry run]' : ''));
            $stats = $sync->syncChanged($dry, (bool) $this->option('full'));
            $this->line('Changes since ' . ($stats['since'] ?? 'the beginning') . ' (UTC); next run reads from ' . $stats['watermark']);
            unset($stats['since'], $stats['watermark']);
        }
        $this->table(array_keys($stats), [array_values($stats)]);
        return 0;
    }
}
