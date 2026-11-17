<?php

namespace App\Console\Commands;

use App\Services\BcPlans\ExecutionPublisher;
use Illuminate\Console\Command;

class PushBcExecutions extends Command
{
    protected $signature = 'bc:push-executions {--limit=500 : orders per run} {--dry-run : list what would be sent}';

    protected $description = 'Send WMS execution orders (with plan no. and transaction id) to the BC staging tables and read their status back';

    public function handle()
    {
        $stats = (new ExecutionPublisher())->run((int) $this->option('limit'), (bool) $this->option('dry-run'));
        $this->info('BC execution orders' . ($this->option('dry-run') ? ' [dry run]' : ''));
        $this->table(array_keys($stats), [array_values($stats)]);
        return $stats['errors'] ? 1 : 0;
    }
}
