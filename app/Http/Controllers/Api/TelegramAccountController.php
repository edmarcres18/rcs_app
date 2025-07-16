<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TelegramAccountController extends Controller
{
    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegramService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\TelegramService  $telegramService
     * @return void
     */
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Link a Telegram account to the authenticated user by username or chat ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function link(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'telegram_username' => 'nullable|string|min:5|max:32',
            'telegram_chat_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // At least one field must be provided
            if (empty($request->telegram_username) && empty($request->telegram_chat_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either telegram username or chat ID must be provided.'
                ], 422);
            }

            // Update the user's Telegram information
            $user->telegram_username = $request->telegram_username ?? $user->telegram_username;
            $user->telegram_chat_id = $request->telegram_chat_id ?? $user->telegram_chat_id;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Telegram account linked successfully.',
                'data' => [
                    'telegram_username' => $user->telegram_username,
                    'telegram_chat_id' => $user->telegram_chat_id,
                    'telegram_notifications_enabled' => $user->telegram_notifications_enabled,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error linking Telegram account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while linking Telegram account.'
            ], 500);
        }
    }

    /**
     * Unlink Telegram account from the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlink(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        try {
            $user->telegram_username = null;
            $user->telegram_chat_id = null;
            $user->telegram_notifications_enabled = false;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Telegram account unlinked successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error unlinking Telegram account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unlinking Telegram account.'
            ], 500);
        }
    }

    /**
     * Toggle Telegram notifications for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$user->hasTelegram()) {
            return response()->json([
                'success' => false,
                'message' => 'No Telegram account linked. Please link your Telegram first.'
            ], 400);
        }

        try {
            // Toggle the current state
            $user->telegram_notifications_enabled = !$user->telegram_notifications_enabled;
            $user->save();

            $status = $user->telegram_notifications_enabled ? 'enabled' : 'disabled';

            // Send a confirmation message to the user's Telegram if notifications were enabled
            if ($user->telegram_notifications_enabled && $user->telegram_chat_id) {
                $this->telegramService->sendMessage(
                    $user->telegram_chat_id,
                    "Notifications have been {$status} for your account. You will now receive MHR Reporting Compliance System notifications via Telegram."
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Telegram notifications {$status} successfully.",
                'data' => [
                    'telegram_notifications_enabled' => $user->telegram_notifications_enabled
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling Telegram notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while toggling Telegram notifications.'
            ], 500);
        }
    }

    /**
     * Send a test notification to the user's linked Telegram account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (empty($user->telegram_chat_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No Telegram chat ID linked. Please link your Telegram account first.'
            ], 400);
        }

        try {
            $result = $this->telegramService->sendMessage(
                $user->telegram_chat_id,
                "Test notification from MHR Reporting Compliance System. Your notifications are working correctly!"
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification. Please check your Telegram account linking.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error sending test Telegram notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending test notification.'
            ], 500);
        }
    }

    /**
     * Get the current user's Telegram account information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'telegram_username' => $user->telegram_username,
                'telegram_chat_id' => $user->telegram_chat_id,
                'telegram_notifications_enabled' => $user->telegram_notifications_enabled,
                'is_linked' => $user->hasTelegram(),
            ]
        ]);
    }
}
