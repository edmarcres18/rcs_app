<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    /**
     * Send a push notification to a user
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param string $url
     * @param string|null $icon
     * @return bool
     */
    public function sendNotification(User $user, string $title, string $body, string $url = '/', ?string $icon = null): bool
    {
        try {
            $subscriptions = PushSubscription::where('user_id', $user->id)->get();

            if ($subscriptions->isEmpty()) {
                return false;
            }

            $webPush = $this->getWebPushClient();

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    $this->createSubscription($subscription),
                    json_encode([
                        'title' => $title,
                        'body' => $body,
                        'icon' => $icon ?? '/images/app_logo/logo.png',
                        'data' => [
                            'url' => $url
                        ]
                    ])
                );
            }

            $results = $webPush->flush();

            // Remove expired subscriptions
            foreach ($results as $result) {
                if (!$result->isSuccess() && $result->isSubscriptionExpired()) {
                    $endpoint = $result->getEndpoint();
                    PushSubscription::where('endpoint', $endpoint)->delete();
                    Log::info('Removed expired push subscription: ' . $endpoint);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send push notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a subscription object from the database model
     *
     * @param PushSubscription $subscription
     * @return Subscription
     */
    private function createSubscription(PushSubscription $subscription): Subscription
    {
        return Subscription::create([
            'endpoint' => $subscription->endpoint,
            'keys' => [
                'p256dh' => $subscription->public_key,
                'auth' => $subscription->auth_token
            ]
        ]);
    }

    /**
     * Get the WebPush client with VAPID configuration
     *
     * @return WebPush
     */
    private function getWebPushClient(): WebPush
    {
        $auth = [
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ];

        return new WebPush($auth);
    }
}
