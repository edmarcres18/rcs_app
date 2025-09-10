<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use App\Models\PendingUpdate;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\UserActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Constructor to apply middleware for role-based access control
     */
    public function __construct()
    {
        // Only ADMIN and SYSTEM_ADMIN can access all user management functions
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->roles, [UserRole::ADMIN, UserRole::SYSTEM_ADMIN])) {
                return response()->view('errors.403', ['message' => 'Unauthorized action.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('roles', '!=', UserRole::SYSTEM_ADMIN);

        // Enhanced search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('roles', $request->role);
        }

        // Email verification filter
        if ($request->filled('email_verified')) {
            if ($request->email_verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->email_verified === 'pending') {
                $query->whereNull('email_verified_at');
            }
        }

        // Date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('first_name', $sortOrder)->orderBy('last_name', $sortOrder);
                break;
            case 'email':
                $query->orderBy('email', $sortOrder);
                break;
            case 'role':
                $query->orderBy('roles', $sortOrder);
                break;
            case 'email_verified':
                if ($sortOrder === 'desc') {
                    $query->orderByRaw('email_verified_at IS NULL, email_verified_at DESC');
                } else {
                    $query->orderByRaw('email_verified_at IS NOT NULL, email_verified_at ASC');
                }
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        // Default to 15 users per page, but allow customization
        $perPage = $request->per_page ?? 15;
        $users = $query->paginate($perPage);

        // Handle AJAX requests for real-time search
        if ($request->ajax() || $request->has('ajax')) {
            $html = '';
            
            if ($users->count() > 0) {
                foreach ($users as $user) {
                    $html .= view('users.partials.user-row', compact('user'))->render();
                }
            } else {
                $html = view('users.partials.no-results', [
                    'hasFilters' => $request->hasAny(['search', 'role', 'email_verified'])
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'pagination_info' => "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} users",
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage()
            ]);
        }

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . Str::slug($validated['first_name']) . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads/avatars'), $filename);
            $validated['avatar'] = 'uploads/avatars/' . $filename;
        }

        $user = User::create($validated);

        // Log the activity
        if (Auth::check()) {
            UserActivityService::logUserCreated(Auth::user(), $user);
        }

        return redirect()->route('users.show', $user)
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Get recent activities for this user
        $activities = $user->activities()
            ->latest()
            ->take(10)
            ->get();

        return view('users.show', compact('user', 'activities'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        if (Auth::user()->roles !== UserRole::SYSTEM_ADMIN) {
            return response()->view('errors.403', ['message' => 'Only System Administrators can edit user details directly.'], 403);
        }
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();
        $currentUser = Auth::user();

        if ($currentUser->roles === UserRole::SYSTEM_ADMIN) {
            // Store old data for activity logging
            $oldData = $user->toArray();

            if (isset($validated['password']) && $validated['password'] !== null) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    unlink(public_path($user->avatar));
                }

                $avatar = $request->file('avatar');
                $filename = time() . '_' . Str::slug($validated['first_name']) . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path('uploads/avatars'), $filename);
                $validated['avatar'] = 'uploads/avatars/' . $filename;
            }

            $user->update($validated);

            // Log the activity
            UserActivityService::logUserUpdated($currentUser, $user, $user->getChanges());

            return redirect()->route('users.show', $user)
                ->with('success', 'User updated successfully!');
        }

        if ($currentUser->roles === UserRole::ADMIN) {
            // Admins cannot update passwords or avatars directly.
            unset($validated['password']);
            if ($request->hasFile('avatar')) {
                return redirect()->back()
                    ->with('error', 'Admins cannot update avatars. Please ask a System Administrator.');
            }

            PendingUpdate::create([
                'user_id' => $user->id,
                'requester_id' => $currentUser->id,
                'type' => 'update',
                'data' => $validated,
            ]);

            return redirect()->route('users.show', $user)
                ->with('success', 'User update request submitted for approval.');
        }

        return response()->view('errors.403', ['message' => 'Unauthorized action.'], 403);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        if ($currentUser->roles === UserRole::SYSTEM_ADMIN) {
            // Store user data for activity logging
            $deletedUser = clone $user;

            // Delete avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // To ensure a hard delete, we can use forceDelete()
            $user->forceDelete();

            // Log the activity
            UserActivityService::logUserDeleted($currentUser, $deletedUser);

            return redirect()->route('users.index')
                ->with('success', 'User permanently deleted successfully!');
        }

        if ($currentUser->roles === UserRole::ADMIN) {
            PendingUpdate::create([
                'user_id' => $user->id,
                'requester_id' => $currentUser->id,
                'type' => 'delete',
            ]);

            return redirect()->route('users.index')
                ->with('success', 'Request to delete user has been submitted for approval.');
        }

        return response()->view('errors.403', ['message' => 'Unauthorized action.'], 403);
    }

    /**
     * Display the activity log for a specific user.
     */
    public function activities(User $user)
    {
        $activities = $user->activities()
            ->latest()
            ->paginate(15);

        return view('users.activities', compact('user', 'activities'));
    }

    /**
     * Display all user activities (admin only).
     */
    public function allActivities(Request $request)
    {
        // This is already protected by the constructor middleware
        // But keeping the check for explicit clarity
        if (!Auth::check() || !in_array(Auth::user()->roles, [UserRole::ADMIN, UserRole::SYSTEM_ADMIN])) {
            return response()->view('errors.403', ['message' => 'Unauthorized action.'], 403);
        }

        $query = UserActivity::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where('activity_description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('activity_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                  });
            })
            ->when($request->type, function ($q, $type) {
                $q->where('activity_type', $type);
            })
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($request->from_date, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($request->to_date, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        $activities = $query->latest()->paginate($request->per_page ?? 15);

        // Get distinct activity types for filter dropdown
        $activityTypes = UserActivity::select('activity_type')
            ->distinct()
            ->pluck('activity_type');

        // Get all users for filter dropdown
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();

        return view('users.all-activities', compact('activities', 'activityTypes', 'users'));
    }
}
