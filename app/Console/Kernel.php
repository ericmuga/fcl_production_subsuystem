<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\RabbitMQService;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(function () {
        //     $rabbitMQService = app(RabbitMQService::class);
        //     if (method_exists($rabbitMQService, 'retryPendingMessages')) {
        //         $rabbitMQService->retryPendingMessages();
        //     } else {
        //         \Log::error('Method retryPendingMessages does not exist in RabbitMQService');
        //     }
        // })->everyTenMinutes();

        // $schedule->command('queue:consume')->everyMinute();

        // BC MRP plan integration over the SQL route (docs/bc-mrp-integration-plan.md); off unless BC_PLAN_SCHEDULE=true
        if (config('bc_plan.schedule')) {
            // Incremental: only what BC inserted or updated since the last run, so it can run every minute.
            $schedule->command('bc:sync-plans')->everyMinute()->withoutOverlapping();
            $schedule->command('bc:push-executions')->everyFiveMinutes()->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
