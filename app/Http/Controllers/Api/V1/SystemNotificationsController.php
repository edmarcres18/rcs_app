<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SystemNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemNotificationsController extends Controller
{
    /**
     * Return active system notifications for non-SYSTEM_ADMIN users.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Exclude SYSTEM_ADMIN from receiving system notifications in the UI
        if ($user->roles === UserRole::SYSTEM_ADMIN) {
            return response()->json([
                'data' => [],
                'count' => 0,
            ]);
        }

        $now = now();

        $query = SystemNotifications::query()
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('date_start')->orWhere('date_start', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', $now);
            })
            ->latest('created_at');

        // Optional limit for performance; default 50
        $limit = (int) $request->get('limit', 50);
        $limit = $limit > 0 && $limit <= 200 ? $limit : 50;

        $notifications = $query->take($limit)->get(['id','title','message','type','status','date_start','date_end','created_at']);

        return response()->json([
            'data' => $notifications->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => (string) $n->title,
                    'message' => (string) $n->message,
                    'type' => (string) $n->type,
                    'status' => (string) $n->status,
                    'date_start' => optional($n->date_start)->toIso8601String(),
                    'date_end' => optional($n->date_end)->toIso8601String(),
                    'created_at' => optional($n->created_at)->toIso8601String(),
                ];
            }),
            'count' => $notifications->count(),
        ]);
    }
}
