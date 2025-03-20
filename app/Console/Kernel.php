<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CleanupActivityLogs;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CleanupActivityLogs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Membersihkan log aktivitas setiap hari pada jam 1 pagi
        $schedule->command('activity:cleanup --days=30 --export-json=true --delete-db=true')
                 ->dailyAt('01:00')
                 ->appendOutputTo(storage_path('logs/activity-cleanup.log'));
    }
}
