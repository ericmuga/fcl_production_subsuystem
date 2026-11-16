<?php

namespace App\Console\Commands;

use App\Services\Stuffing\StuffingHarmonizer;
use Illuminate\Console\Command;

/**
 * End-of-day catch-up for review mode: harmonize every batch not yet released, and
 * optionally release it, so orders never sit waiting for someone to open the screen.
 */
class HarmonizeStuffingBatches extends Command
{
    protected $signature = 'stuffing:harmonize
                            {--from= : First production date, YYYY-MM-DD. Defaults to yesterday}
                            {--to= : Last production date, YYYY-MM-DD. Defaults to today}
                            {--mode= : recipe | output_at_last | output_at_first. Defaults to PRODUCTION_ORDERS_HARMONIZE_MODE}
                            {--release : Also write the batches to ProductionData}
                            {--dry-run : Show the batches and what each would do without saving}
                            {--force : Skip the confirmation prompt for --release}';

    protected $description = 'Harmonize stuffing production orders with the dispatch transfer weights, per batch and packed item';

    public function handle(StuffingHarmonizer $harmonizer)
    {
        $from = $this->option('from') ?: now()->subDay()->toDateString();
        $to = $this->option('to') ?: now()->toDateString();
        $mode = $this->option('mode') ?: StuffingHarmonizer::defaultMode();
        if (!isset(StuffingHarmonizer::MODES[$mode])) {
            $this->error("Unknown mode {$mode}. Use " . implode(', ', array_keys(StuffingHarmonizer::MODES)) . '.');

            return 1;
        }
        $release = (bool) $this->option('release');

        $this->line("Range: {$from} to {$to}");
        $this->line("Mode:  {$mode} - " . StuffingHarmonizer::MODES[$mode]);
        $this->line('Write: ' . ($release ? config('production_orders.target') : 'no (save only)'));

        if ($this->option('dry-run')) {
            $rows = $harmonizer->batches($from, $to, null, 'open')->map(function ($b) use ($harmonizer, $mode) {
                $p = $harmonizer->plan($b->batch_no, $b->packed_item, $mode, $b->dispatched_kg);

                return [$b->batch_no, $b->packed_item, $b->weighings, round($b->stuffed_kg, 2), round($p['expected_out'], 2),
                    round($b->dispatched_kg, 2), $b->state, implode('; ', $p['errors'])];
            });
            $this->table(['Batch', 'Packed item', 'Weighings', 'In kg', 'Recipe out kg', 'Dispatched kg', 'State', 'Notes'], $rows);

            return 0;
        }

        if ($release && config('production_orders.target') === 'production_data' && !$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm('This writes the harmonized batches into the BC ProductionData table. Continue?', false)) {
                $this->info('Nothing done.');

                return 0;
            }
        }

        $summary = $harmonizer->run($from, $to, $mode, $release, 'WMS', function ($b, $msg) {
            $this->line("{$b->batch_no} / {$b->packed_item}: {$msg}");
        });
        $this->info("{$summary['batches']} batch(es): {$summary['applied']} harmonized, {$summary['skipped']} skipped, {$summary['failed']} failed.");

        return $summary['failed'] ? 1 : 0;
    }
}
