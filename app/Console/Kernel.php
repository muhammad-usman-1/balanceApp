<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Activate queued subscriptions whose parent plan ended yesterday
        $schedule->command('subscriptions:activate-queued')->dailyAt('00:05');

        // Queue auto-renewal for plans expiring within 3 days
        $schedule->command('subscriptions:auto-renew')->dailyAt('00:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
