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
                // Delete old avatar if exists
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    unlink(public_path($user->avatar));
                }

                $avatar = $request->file('avatar');
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('uploads/avatars');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Generate unique filename
                $filename = time() . '_' . Str::slug($validated['first_name']) . '.' . $avatar->getClientOriginalExtension();
                
                // Optimize image if it's large (over 1MB)
                if ($avatar->getSize() > 1024 * 1024) {
                    // Use Intervention Image if available, otherwise use basic PHP
                    if (class_exists('\Intervention\Image\Facades\Image')) {
                        $img = \Intervention\Image\Facades\Image::make($avatar->getRealPath());
                        $img->resize(800, 800, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                        $img->save($uploadPath . '/' . $filename, 80);
                    } else {
                        // Fallback to basic move with no optimization
                        $avatar->move($uploadPath, $filename);
                    }
                } else {
                    // For smaller images, just move the file
                    $avatar->move($uploadPath, $filename);
                }
                
                $validated['avatar'] = 'uploads/avatars/' . $filename;
            } catch (\Exception $e) {
                // Log the error
                \Illuminate\Support\Facades\Log::error('Avatar upload failed: ' . $e->getMessage());
                
                // Continue without avatar update
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['avatar' => 'Failed to upload avatar. Please try again with a smaller image.']);
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
