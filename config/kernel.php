<?php

namespace App\Console;

use App\Services\TokenService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Refill token free user setiap jam (cek per jam, proses yang sudah waktunya)
        $schedule->call(function () {
            app(TokenService::class)->refillEligibleUsers();
        })->hourly()->name('token-refill')->withoutOverlapping();

        // Downgrade expired premium plans setiap hari jam 00:05
        $schedule->call(function () {
            app(TokenService::class)->checkExpiredPlans();
        })->dailyAt('00:05')->name('check-expired-plans')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}