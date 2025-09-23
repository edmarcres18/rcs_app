<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemNotifications;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\TelegramService;

class SystemNotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:' . UserRole::SYSTEM_ADMIN->value);
    }

    public function index(Request $request)
    {
        $query = SystemNotifications::with('createdBy');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        $notifications = $query->latest()->paginate(10);
        
        // Handle AJAX requests for live filtering
        if ($request->ajax()) {
            $tableHtml = '';
            
            if ($notifications->count() > 0) {
                foreach ($notifications as $notification) {
                    $tableHtml .= '
                        <tr>
                            <td>
                                <div class="fw-semibold">' . e($notification->title) . '</div>
                                <small class="text-muted">' . e(\Illuminate\Support\Str::limit($notification->message, 50)) . '</small>
                            </td>
                            <td>
                                <span class="badge type-badge type-' . $notification->type . '">
                                    ' . ucfirst($notification->type) . '
                                </span>
                            </td>
                            <td>
                                <span class="badge status-badge status-' . $notification->status . '">
                                    ' . ucfirst($notification->status) . '
                                </span>
                            </td>
                            <td>
                                <small>' . ($notification->date_start ? $notification->date_start->format('M d, Y H:i') : 'N/A') . '</small>
                            </td>
                            <td>
                                <small>' . ($notification->date_end ? $notification->date_end->format('M d, Y H:i') : 'N/A') . '</small>
                            </td>
                            <td>
                                <small>' . e($notification->createdBy ? $notification->createdBy->name : 'Unknown User') . '</small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="' . route('admin.system-notifications.edit', $notification) . '" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger delete-btn" 
                                                data-id="' . $notification->id . '"
                                                data-title="' . e($notification->title) . '"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>';
                }
            } else {
                $hasFilters = $request->hasAny(['search', 'type', 'status', 'date_from']);
                $tableHtml = '
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">No system notifications found.</p>';
                
                if ($hasFilters) {
                    $tableHtml .= '<small>Try adjusting your filters or <a href="' . route('admin.system-notifications.index') . '">clear all filters</a>.</small>';
                }
                
                $tableHtml .= '
                            </div>
                        </td>
                    </tr>';
            }
            
            // Generate pagination HTML
            $paginationHtml = '';
            if ($notifications->hasPages()) {
                $paginationHtml = '
                    <div class="text-muted">
                        Showing ' . $notifications->firstItem() . ' to ' . $notifications->lastItem() . ' of ' . $notifications->total() . ' results
                    </div>
                    <div>
                        ' . $notifications->appends($request->query())->links()->toHtml() . '
                    </div>';
            }
            
            return response()->json([
                'table_html' => $tableHtml,
                'pagination_html' => $paginationHtml,
                'total' => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]);
        }
        
        return view('system-notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('system-notifications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,update,maintenance,alert',
            'status' => 'required|in:active,inactive,archived',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        SystemNotifications::create($request->all() + ['created_by' => Auth::id()]);

        return redirect()->route('admin.system-notifications.index')
            ->with('success', 'System notification created successfully.');
    }

    public function edit(SystemNotifications $systemNotification)
    {
        return view('system-notifications.edit', ['notification' => $systemNotification]);
    }

    public function update(Request $request, SystemNotifications $systemNotification)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,update,maintenance,alert',
            'status' => 'required|in:active,inactive,archived',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
        ]);

        $systemNotification->update($request->all());

        return redirect()->route('admin.system-notifications.index')
            ->with('success', 'System notification updated successfully.');
    }

    /**
     * Immediately send this system notification to users who linked Telegram chat IDs.
     */
    public function sendNow(SystemNotifications $systemNotification, TelegramService $telegram)
    {
        // Validate status and date window
        if ($systemNotification->status !== 'active') {
            return back()->with('error', 'Notification must be active to send.');
        }

        $now = now();
        if (($systemNotification->date_start && $systemNotification->date_start->isFuture()) ||
            ($systemNotification->date_end && $systemNotification->date_end->isPast())) {
            return back()->with('error', 'Notification is not within its active schedule window.');
        }

        // Build Telegram message using MarkdownV2 formatting similar to SystemBroadcastNotification
        $title = (string) $systemNotification->title;
        $type = (string) ($systemNotification->type ?? 'info');
        $body = (string) $systemNotification->message;

        $escape = function (string $text): string {
            $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
            foreach ($chars as $ch) {
                $text = str_replace($ch, '\\' . $ch, $text);
            }
            return $text;
        };

        $lines = [
            "\u{1F514} *System Notification*",
            '*Title:* ' . $escape($title),
            '*Type:* ' . ucfirst($type),
            '',
            $escape($body),
        ];

        $text = implode("\n", $lines);

        // Collect recipients: all users except SYSTEM_ADMIN with telegram chat id and enabled
        $recipients = User::query()
            ->whereIn('roles', [
                UserRole::EMPLOYEE->value,
                UserRole::SUPERVISOR->value,
                UserRole::ADMIN->value,
            ])
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->where('telegram_notifications_enabled', true)
            ->pluck('telegram_chat_id')
            ->all();

        if (empty($recipients)) {
            return back()->with('warning', 'No recipients with Telegram chat IDs found.');
        }

        DB::beginTransaction();
        try {
            // Broadcast via Telegram
            $telegram->broadcastMessage($recipients, $text, [
                'parse_mode' => 'MarkdownV2',
                'disable_web_page_preview' => true,
            ]);

            // Mark as notified
            $systemNotification->notified_at = now();
            $systemNotification->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to send system notification via Telegram', [
                'notification_id' => $systemNotification->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to send Telegram messages.');
        }

        return back()->with('success', 'System notification sent to Telegram recipients.');
    }

    public function destroy(SystemNotifications $systemNotification)
    {
        $systemNotification->delete();

        return redirect()->route('admin.system-notifications.index')
            ->with('success', 'System notification deleted successfully.');
    }
}
