<?php

namespace App\Console;

use App\Models\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('announcements:process')->everyMinute();

        $schedule->call(function (): void {
            Notification::query()->where('created_at', '<', now()->subMonths(3))->delete();
            Log::info('Cleaned up old notification logs');
        })->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
