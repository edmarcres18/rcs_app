<?php

namespace App\Notifications;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The message content.
     *
     * @var string
     */
    protected $content;

    /**
     * Create a new notification instance.
     *
     * @param string $content
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Get the notification channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['telegram'];
    }

    /**
     * Get the Telegram representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toTelegram($notifiable)
    {
        return [
            'content' => $this->content,
        ];
    }
}
