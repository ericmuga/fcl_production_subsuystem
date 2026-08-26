<?php

namespace App\Console\Commands;

use App\Http\Controllers\SausageController;
use Illuminate\Console\Command;

class BackfillProductionOrders extends Command
{
    protected $signature = 'stuffing:backfill-orders
                            {--from= : First date to catch up, YYYY-MM-DD. Defaults to today}
                            {--to= : Last date to catch up, YYYY-MM-DD. Defaults to today}
                            {--dry-run : List what would be generated without writing anything}
                            {--force : Skip the confirmation prompt (implied when not interactive)}';

    protected $description = 'Generate production orders for stuffing weighings from a date that never got any';

    public function handle(SausageController $sausage)
    {
        $from = $this->option('from') ?: now()->toDateString();
        $to = $this->option('to') ?: now()->toDateString();
        $dryRun = (bool) $this->option('dry-run');

        $recipeTable = config('production_orders.recipe_table', config('recipes.table', 'RecipeData'));
        $target = config('production_orders.target');

        $this->line("Range:   {$from} to {$to}");
        $this->line("Recipes: {$recipeTable}");
        $this->line('Target:  ' . ($target === 'production_data'
            ? config('production_orders.production_data_table', 'ProductionData') . ' (BC) + generated_production_orders'
            : 'generated_production_orders only (local)'));

        // Writing into BC is the one thing here that is not ours to undo, so it gets
        // said out loud before anything runs.
        if (!$dryRun && $target === 'production_data' && !$this->option('force')) {
            if (!$this->confirm('This will write into the BC ProductionData table. Continue?', false)) {
                $this->info('Nothing done.');

                return 0;
            }
        }

        $this->newLine();

        try {
            $summary = $sausage->backfillProductionOrders($from, $to, $dryRun, function ($weighing, $result) {
                $when = \Carbon\Carbon::parse($weighing->created_at)->format('Y-m-d H:i');

                $this->line(sprintf(
                    '  #%-7s %s  %-10s -> %-10s %8.2f kg  %s',
                    $weighing->id,
                    $when,
                    $weighing->product_code,
                    $weighing->description,
                    (float) $weighing->total_weight,
                    $result['message']
                ));
            });
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->newLine();

        if ($summary['candidates'] === 0) {
            $this->info('No weighings without production orders in that range.');

            return 0;
        }

        $this->table(
            ['Weighings without orders', 'Generated', 'Orders', 'Skipped', 'Failed'],
            [[$summary['candidates'], $summary['generated'], $summary['orders'], $summary['skipped'], $summary['failed']]]
        );

        if ($dryRun) {
            $this->info('Dry run - nothing was written. Re-run without --dry-run to generate.');
        }

        return $summary['failed'] > 0 ? 1 : 0;
    }
}
