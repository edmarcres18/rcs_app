<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $this->validate($request, [
                'endpoint' => 'required|string',
                'keys.p256dh' => 'required|string',
                'keys.auth' => 'required|string',
            ]);

            // Check if subscription already exists
            $subscription = PushSubscription::where('endpoint', $request->endpoint)
                ->where('user_id', $user->id)
                ->first();

            if (!$subscription) {
                // Create new subscription
                $subscription = new PushSubscription([
                    'user_id' => $user->id,
                    'endpoint' => $request->endpoint,
                    'public_key' => $request->keys['p256dh'],
                    'auth_token' => $request->keys['auth'],
                ]);

                $subscription->save();
            }

            return response()->json(['success' => true, 'message' => 'Subscription saved successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to save push subscription: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save subscription'], 500);
        }
    }

    /**
     * Delete a push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $this->validate($request, [
                'endpoint' => 'required|string',
            ]);

            // Delete the subscription
            PushSubscription::where('endpoint', $request->endpoint)
                ->where('user_id', $user->id)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Subscription deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete push subscription: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete subscription'], 500);
        }
    }
}
