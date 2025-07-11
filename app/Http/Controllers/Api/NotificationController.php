<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(15);

        return NotificationResource::collection($notifications);
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
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
    }

    /**
     * Mark all unread notifications as read.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }

    /**
     * Get unread notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unread(Request $request): JsonResponse
    {
        $unreadNotifications = Auth::user()->unreadNotifications;

        return response()->json([
            'success' => true,
            'count' => $unreadNotifications->count(),
            'notifications' => NotificationResource::collection($unreadNotifications)
        ]);
    }
}
