<?php

namespace App\Notifications\Channels;

use App\Services\TelegramService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TelegramChannel
{
    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegram;

    /**
     * Create a new Telegram channel instance.
     *
     * @param  \App\Services\TelegramService  $telegram
     * @return void
     */
    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notifiable, 'routeNotificationForTelegram')) {
            $chatId = $notifiable->routeNotificationForTelegram($notification);
        } elseif (isset($notifiable->telegram_chat_id)) {
            $chatId = $notifiable->telegram_chat_id;
        } else {
            Log::warning('No Telegram chat ID found for notifiable', [
                'notifiable' => get_class($notifiable),
                'id' => $notifiable->getKey(),
            ]);
            return;
        }

        if (!method_exists($notification, 'toTelegram')) {
            Log::warning('Notification does not have toTelegram method', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        $message = $notification->toTelegram($notifiable);

        if (is_string($message)) {
            $this->telegram->sendMessage($chatId, $message);
        } elseif (is_array($message) && isset($message['content'])) {
            $this->telegram->sendMessage($chatId, $message['content']);
        } else {
            Log::warning('Invalid Telegram notification format', [
                'notification' => get_class($notification),
                'message' => $message,
            ]);
        }
    }
}
 