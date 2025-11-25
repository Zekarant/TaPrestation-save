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
        // Update equipment rental statuses daily at midnight
        $schedule->command('rentals:update-statuses')
                 ->daily()
                 ->at('00:01')
                 ->appendOutputTo(storage_path('logs/rental-status-updates.log'));
                 
        // Check for password reset emails every 5 minutes
        $schedule->command('imap:check-password-resets --notify')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/password-reset-checks.log'));
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