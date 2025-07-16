<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationController extends Controller
{
    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService|null
     */
    protected $telegramService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\TelegramService|null  $telegramService
     * @return void
     */
    public function __construct(TelegramService $telegramService = null)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Get all notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $notifications = $user->notifications()->latest()->paginate(15);
            return NotificationResource::collection($notifications);
        } catch (Throwable $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching notifications.'], 500);
        }
    }

    /**
     * Mark a specific notification as read.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Notifications\DatabaseNotification $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        try {
            $this->authorize('update', $notification);

            $notification->markAsRead();

            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        } catch (Throwable $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while marking the notification as read.'], 500);
        }
    }

    /**
     * Mark all unread notifications as read.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $user->unreadNotifications->markAsRead();

            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        } catch (Throwable $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while marking all notifications as read.'], 500);
        }
    }

    /**
     * Get the count of unread notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'count' => $request->user()->unreadNotifications()->count(),
            ]);
        } catch (Throwable $e) {
            Log::error('Error fetching unread notification count: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching unread notification count.'], 500);
        }
    }

    /**
     * Get unread notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unread(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            $unreadNotifications = $user->unreadNotifications;

            return response()->json([
                'success' => true,
                'count' => $unreadNotifications->count(),
                'notifications' => NotificationResource::collection($unreadNotifications)
            ]);
        } catch (Throwable $e) {
            Log::error('Error fetching unread notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching unread notifications.'], 500);
        }
    }

    /**
     * Send a notification to the user via Telegram.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTelegramNotification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            // Validate request
            $request->validate([
                'message' => 'required|string|max:4096',
            ]);

            // Check if user has Telegram configured and enabled
            if (!$user->telegram_chat_id || !$user->telegram_notifications_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Telegram notifications are not enabled for this user.'
                ], 400);
            }

            // Send message
            if ($this->telegramService) {
                $result = $this->telegramService->sendMessage($user->telegram_chat_id, $request->message);

                if ($result) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Telegram notification sent successfully.'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send Telegram notification.'
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => 'Telegram service is not available.'
            ], 500);
        } catch (Throwable $e) {
            Log::error('Error sending Telegram notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the Telegram notification.'
            ], 500);
        }
    }
}
