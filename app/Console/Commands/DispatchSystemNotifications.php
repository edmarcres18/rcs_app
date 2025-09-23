<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SystemNotifications;
use App\Models\User;
use App\Enums\UserRole;
use App\Notifications\SystemBroadcastNotification;

class DispatchSystemNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-notifications:dispatch-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch system notifications (email/telegram) when start date is due for non-SYSTEM_ADMIN users.';

    public function handle(): int
    {
        $now = now();

        $due = SystemNotifications::query()
            ->where('status', 'active')
            ->whereNull('notified_at')
            ->where(function ($q) use ($now) {
                $q->whereNull('date_start')->orWhere('date_start', '<=', $now);
            })
            ->orderBy('id')
            ->get();

        if ($due->isEmpty()) {
            $this->info('No due system notifications found.');
            return self::SUCCESS;
        }

        // Recipients: all users except SYSTEM_ADMIN
        $recipients = User::query()
            ->whereIn('roles', [
                UserRole::EMPLOYEE->value,
                UserRole::SUPERVISOR->value,
                UserRole::ADMIN->value,
            ])
            ->select(['id', 'name', 'email', 'telegram_chat_id', 'telegram_username', 'telegram_notifications_enabled'])
            ->get();

        foreach ($due as $notification) {
            $title = (string) $notification->title;
            $message = (string) $notification->message;
            $type = (string) ($notification->type ?? 'info');

            $this->info("Dispatching notification ID {$notification->id} to {$recipients->count()} users...");

            DB::beginTransaction();
            try {
                // Notify each user (queued via ShouldQueue on the notification)
                $recipients->chunk(200)->each(function ($chunk) use ($title, $message, $type) {
                    foreach ($chunk as $user) {
                        try {
                            $user->notify(new SystemBroadcastNotification($title, $message, $type));
                        } catch (\Throwable $e) {
                            Log::warning('Failed to queue system notification for user', [
                                'user_id' => $user->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });

                // Mark as notified to avoid duplicate sends
                $notification->notified_at = now();
                $notification->save();

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to dispatch system notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next notification instead of failing entire run
                continue;
            }
        }

        $this->info('Dispatch complete.');
        return self::SUCCESS;
    }
}
