<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
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
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        // Only SYSTEM_ADMIN can delete users
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->roles !== UserRole::SYSTEM_ADMIN) {
                abort(403, 'Only System Administrators can delete users.');
            }
            return $next($request);
        })->only(['destroy']);
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->per_page ?? 15);

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
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

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
        if (Auth::check()) {
            UserActivityService::logUserUpdated(Auth::user(), $user, $oldData);
        }

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     * Only SYSTEM_ADMIN can access this method.
     */
    public function destroy(User $user)
    {
        // Store user data for activity logging
        $deletedUser = clone $user;

        // Delete avatar if exists
        if ($user->avatar && file_exists(public_path($user->avatar))) {
            unlink(public_path($user->avatar));
        }

        $user->delete();

        // Log the activity
        if (Auth::check()) {
            UserActivityService::logUserDeleted(Auth::user(), $deletedUser);
        }

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
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
            abort(403, 'Unauthorized action.');
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
