<?php

namespace App\Notifications;

use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;

class RealTimeNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $message;
    public $link;
    public $type;
    public $title;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $link = null, $type = 'info', $title = null)
    {
        $this->message = $message;
        $this->link = $link;
        $this->type = $type;
        $this->title = $title ?? 'New Notification';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database', 'custom-webpush'];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
            'type' => $this->type,
            'time' => now()->toDateTimeString()
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
            'type' => $this->type,
            'time' => now()->toDateTimeString()
        ];
    }

    /**
     * Send the notification to WebPush.
     *
     * @param mixed $notifiable
     * @return void
     */
    public function toWebPush($notifiable)
    {
        try {
            $webPushService = app(WebPushService::class);
            $webPushService->sendNotification(
                $notifiable,
                $this->title,
                $this->message,
                $this->link ?? '/',
                null // Use default icon
            );
        } catch (\Exception $e) {
            Log::error('Failed to send web push notification: ' . $e->getMessage());
        }
    }
}
