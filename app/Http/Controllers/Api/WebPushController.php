<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WebPushController extends Controller
{
    /**
     * Get the VAPID public key for push notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVapidPublicKey(): JsonResponse
    {
        $vapidPublicKey = config('services.webpush.public_key');

        if (!$vapidPublicKey) {
            return response()->json(['error' => 'VAPID public key not configured'], 500);
        }

        return response()->json(['vapidPublicKey' => $vapidPublicKey]);
    }
}
