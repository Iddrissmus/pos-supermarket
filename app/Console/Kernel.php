<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckReorderLevels;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CheckReorderLevels::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run hourly by default. Users can change this in their server cron.
        $schedule->command('stock:check-reorder')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // load from routes/console.php if present
        if (file_exists($this->app->basePath('routes/console.php'))) {
            require $this->app->basePath('routes/console.php');
        }
    }
}
