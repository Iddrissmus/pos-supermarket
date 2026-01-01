<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckReorderLevels;
use App\Console\Commands\BackupDatabase;
use App\Console\Commands\SendDailySalesSummary;
use App\Console\Commands\CheckExpiringProducts;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CheckReorderLevels::class,
        BackupDatabase::class,
        SendDailySalesSummary::class,
        CheckExpiringProducts::class,
        \App\Console\Commands\ProcessRecurringInvoices::class,
        \App\Console\Commands\SendInvoiceReminders::class,
        \App\Console\Commands\SendScheduledInvoices::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run hourly by default. Users can change this in their server cron.
        $schedule->command('stock:check-reorder')->hourly();
        
        // Send daily sales summary at midnight (for previous day)
        $schedule->command('sales:daily-summary')->dailyAt('00:00');
        
        // Check for expiring products daily at 8 AM
        $schedule->command('products:check-expiring')->dailyAt('08:00');

        // Invoice Automation
        $schedule->command('invoices:process-recurring')->dailyAt('01:00');
        $schedule->command('invoices:send-reminders')->dailyAt('09:00');
        $schedule->command('invoices:send-scheduled')->everyFiveMinutes(); // Check frequently
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
