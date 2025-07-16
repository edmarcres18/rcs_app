<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WebPushChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toWebPush')) {
            try {
                $notification->toWebPush($notifiable);
            } catch (\Exception $e) {
                Log::error('Error sending web push notification: ' . $e->getMessage());
            }
        }
    }
}
