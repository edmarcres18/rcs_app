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
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('instructions:send-reminders')->daily();
        $schedule->command('telegram:verify-webhook')->everyFifteenMinutes();
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
