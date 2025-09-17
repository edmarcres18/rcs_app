<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user profile page.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $activities = $user->activities()->latest()->take(5)->get();

        return view('profile.show', compact('user', 'activities'));
    }

    /**
     * Show the form for editing the profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile information.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validated();

        // Store old data for activity logging
        $oldData = $user->getAttributes();

        // Handle the avatar upload if provided
        if ($request->hasFile('avatar')) {
            try {
                $avatar = $request->file('avatar');
                
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                    Storage::disk('public')->delete('avatars/' . $user->avatar);
                }

                // Generate unique filename
                $filename = time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
                
                // Store the uploaded file using Laravel Storage
                $path = $avatar->storeAs('avatars', $filename, 'public');
                
                if ($path) {
                    $validated['avatar'] = $filename;
                } else {
                    return back()->withErrors([
                        'avatar' => 'Failed to upload avatar. Please try again.'
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Avatar upload failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'file_size' => $avatar->getSize(),
                    'file_type' => $avatar->getMimeType(),
                    'original_name' => $avatar->getClientOriginalName()
                ]);
                return back()->withErrors([
                    'avatar' => 'Failed to upload avatar. Please try again.'
                ]);
            }
        }

        $user->update($validated);

        // Log the activity
        UserActivityService::log(
            'profile_updated',
            'Profile information updated',
            [
                'changes' => $user->getChanges(),
            ]
        );

        return redirect()->route('profile.show')
            ->with('success', 'Your profile has been successfully updated.');
    }

    /**
     * Show the form for changing password.
     *
     * @return \Illuminate\View\View
     */
    public function changePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided password does not match your current password.',
            ]);
        }

        // Update the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Log the activity
        UserActivityService::log(
            'password_changed',
            'Password changed successfully'
        );

        return redirect()->route('profile.show')
            ->with('success', 'Your password has been successfully updated.');
    }
}
