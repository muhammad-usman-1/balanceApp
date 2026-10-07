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
        // Expire subscriptions whose end_date has passed
        $schedule->command('subscriptions:expire')->dailyAt('00:00');

        // Activate queued subscriptions (reorders/renewals) once their parent plan
        // has ended and their own start_date has arrived. Must stay on even while
        // auto-renew is disabled — customers can still reorder manually, and
        // without this those subscriptions stay "queued" forever. Runs hourly
        // (idempotent) so a missed midnight run doesn't leave a customer waiting.
        $schedule->command('subscriptions:activate-queued')->hourlyAt(5);

        // Auto-renew is disabled for now (feature isn't ready to ship yet —
        // re-enable once it is):
        // Queue auto-renewal for plans expiring within 3 days
        // $schedule->command('subscriptions:auto-renew')->dailyAt('00:10');

        // Auto-resume subscriptions whose single-day or multi-day pause has ended
        $schedule->command('subscriptions:auto-resume')->dailyAt('00:01');
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
