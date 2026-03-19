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
                 
        // Process expired escrows (auto-release after 48h)
        $schedule->command('escrow:process-expired')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/escrow-auto-release.log'));
                 
        // Rembourser les commandes food dont le code a expiré (24h sans validation)
        $schedule->command('food:refund-expired')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/food-expired-refunds.log'));

        // Rappel J-1 des commandes food planifiées (requested_at)
        $schedule->command('food:send-reminders-tomorrow')
             ->hourly()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/food-tomorrow-reminders.log'));

        // Rappel 4h avant commandes food planifiées (client + prestataire)
        $schedule->command('food:send-reminders-4h')
             ->everyFifteenMinutes()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/food-4h-reminders.log'));

           // Jour J: bascule automatique accepted -> preparing -> ready (commandes planifiées)
           $schedule->command('food:process-scheduled')
               ->everyFiveMinutes()
               ->withoutOverlapping()
               ->appendOutputTo(storage_path('logs/food-process-scheduled.log'));

        // Rappel 4h avant réservations de service (client + prestataire)
        $schedule->command('bookings:send-reminders-4h')
             ->everyFifteenMinutes()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/booking-4h-reminders.log'));

        // Rappel J-1 locations d'équipement (client + prestataire)
        $schedule->command('rentals:send-reminders')
             ->hourly()
             ->withoutOverlapping()
             ->appendOutputTo(storage_path('logs/rental-reminders.log'));
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