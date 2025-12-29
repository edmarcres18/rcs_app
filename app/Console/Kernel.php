<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GenerateVapidKeys::class,
        Commands\SendDeadlineReminders::class,
        Commands\SendInstructionReminders::class,
        Commands\SetupTelegramWebhook::class,
        Commands\TestTelegramMessage::class,
        Commands\PollTelegramUpdates::class,
        Commands\TelegramWebhookInfo::class,
        Commands\DeleteTelegramWebhook::class,
        Commands\VerifyAndRepairTelegramWebhook::class,
        Commands\GenerateUserActivityWrapped::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('instructions:send-reminders')->daily();
        $schedule->command('telegram:verify-webhook')->everyFifteenMinutes();
        // Daily database backup
        $schedule->command('database:backup')->dailyAt('04:00')->timezone('Asia/Manila');

        // Dispatch due system notifications (email/telegram)
        $schedule->command('system-notifications:dispatch-due')
            ->everyMinute()
            ->timezone('Asia/Manila');

        // Yearly user activity Wrapped (Jan 2 01:00, Manila) to avoid Jan 1 peak load
        $schedule->command('user-activity:wrapped')
            ->yearlyOn(1, 2, '01:00')
            ->timezone('Asia/Manila');
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
