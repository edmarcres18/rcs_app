<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\TelegramChannel;

class SystemBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info',
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        // Use custom Telegram channel if user has chat id (handled by routeNotificationForTelegram)
        $channels[] = TelegramChannel::class;

        // You can also add 'broadcast' or 'database' if needed
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = '[System Notification] ' . $this->title;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . ($notifiable->full_name ?? $notifiable->name ?? 'User'))
            ->line($this->message)
            ->salutation('— ' . config('app.name'));
    }

    public function toTelegram(object $notifiable): array|string|null
    {
        // Format a clean Telegram message
        $lines = [
            "\u{1F514} *System Notification*",
            '*Title:* ' . $this->escapeMarkdownV2($this->title),
            '*Type:* ' . ucfirst($this->type),
            '',
            $this->escapeMarkdownV2($this->message),
        ];

        return [
            'text' => implode("\n", $lines),
            'parse_mode' => 'MarkdownV2',
        ];
    }

    private function escapeMarkdownV2(string $text): string
    {
        // Minimal escaping for Telegram MarkdownV2
        $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        foreach ($chars as $ch) {
            $text = str_replace($ch, '\\' . $ch, $text);
        }
        return $text;
    }
}
