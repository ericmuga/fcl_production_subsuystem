<?php

namespace App\Console\Commands;

use App\Http\Controllers\SlaughterController;
use Illuminate\Console\Command;

class ConsumeQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from the queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = new SlaughterController();
        $controller->consumeFromQueue();
        return 0;
    }
}
