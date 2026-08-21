<?php

namespace App\Console\Commands;

use App\Services\RecipeDraftReplicator;
use Illuminate\Console\Command;

class ReplicateRecipeDraft extends Command
{
    protected $signature = 'recipes:replicate-draft
                            {--if-empty : Only copy when the draft is empty, so edits in progress are never discarded}
                            {--force : Skip the confirmation prompt (implied when not interactive)}';

    protected $description = 'Fill recipe_data_draft with a fresh copy of the live RecipeData table';

    public function handle(RecipeDraftReplicator $replicator)
    {
        $draft = $replicator->draftTable();
        $live = $replicator->liveTable();

        try {
            $liveCount = $replicator->liveCount();
            $draftCount = $replicator->draftCount();
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->line("Source: {$live} (" . number_format($liveCount) . ' line(s))');
        $this->line("Target: {$draft} (" . number_format($draftCount) . ' line(s))');

        if ($this->option('if-empty') && $draftCount > 0) {
            $this->info("Draft already populated - nothing to do.");

            return 0;
        }

        // Replacing a draft someone has been editing loses their work, so say so
        // before doing it.
        if ($draftCount > 0 && !$this->option('force')
            && !$this->confirm("This replaces all {$draftCount} line(s) in {$draft}. Continue?", false)) {
            $this->comment('Aborted.');

            return 0;
        }

        try {
            $result = $replicator->replicate($this->option('if-empty'));
        } catch (\Exception $e) {
            $this->error('Replication failed: ' . $e->getMessage());

            return 1;
        }

        if ($result['skipped']) {
            $this->info($result['reason']);

            return 0;
        }

        $this->info('Copied ' . number_format($result['copied']) . " line(s) into {$draft}.");

        if ($result['copied'] !== $liveCount) {
            $this->warn("Expected {$liveCount} - check the source table for concurrent changes.");
        }

        $this->line('Recipe caches cleared.');

        return 0;
    }
}
