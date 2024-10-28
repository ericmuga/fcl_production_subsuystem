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
        $schedule->call(function () {
            $rabbitMQService = app(RabbitMQService::class);
            if (method_exists($rabbitMQService, 'retryPendingMessages')) {
                $rabbitMQService->retryPendingMessages();
            } else {
                \Log::error('Method retryPendingMessages does not exist in RabbitMQService');
            }
        })->everyTenMinutes();

        $schedule->command('queue:consume')->everyTenMinutes();
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
