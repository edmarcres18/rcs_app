<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemNotifications;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

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

    public function destroy(SystemNotifications $systemNotification)
    {
        $systemNotification->delete();

        return redirect()->route('admin.system-notifications.index')
            ->with('success', 'System notification deleted successfully.');
    }
}
