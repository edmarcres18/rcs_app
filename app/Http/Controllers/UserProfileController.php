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
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Exception;

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
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $validated = $request->validated();

            // Store old data for activity logging
            $oldData = $user->getAttributes();

            // Handle the avatar upload if provided
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->handleAvatarUpload($request->file('avatar'), $user, $validated['first_name']);
                if ($avatarPath) {
                    $validated['avatar'] = $avatarPath;
                } else {
                    return redirect()->back()
                        ->withErrors(['avatar' => 'Failed to upload avatar. Please try again.'])
                        ->withInput();
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

        } catch (Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['general' => 'An error occurred while updating your profile. Please try again.'])
                ->withInput();
        }
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

    /**
     * Handle avatar upload with optimization and security checks.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  \App\Models\User  $user
     * @param  string  $firstName
     * @return string|null
     */
    private function handleAvatarUpload($file, $user, $firstName)
    {
        try {
            // Validate file size (double-check server-side)
            if ($file->getSize() > 10485760) { // 10MB in bytes
                Log::warning('Avatar upload failed: File too large', [
                    'user_id' => $user->id,
                    'file_size' => $file->getSize()
                ]);
                return null;
            }

            // Validate MIME type for security
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                Log::warning('Avatar upload failed: Invalid MIME type', [
                    'user_id' => $user->id,
                    'mime_type' => $file->getMimeType()
                ]);
                return null;
            }

            // Create uploads directory if it doesn't exist
            $uploadPath = public_path('uploads/avatars');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // Generate secure filename
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::slug($firstName) . '_' . Str::random(8) . '.' . $extension;
            $relativePath = 'uploads/avatars/' . $filename;
            $fullPath = public_path($relativePath);

            // Move the uploaded file
            if (!$file->move($uploadPath, $filename)) {
                Log::error('Avatar upload failed: Could not move file', [
                    'user_id' => $user->id,
                    'filename' => $filename
                ]);
                return null;
            }

            // Optimize image if Intervention Image is available
            if (class_exists('Intervention\\Image\\Facades\\Image')) {
                try {
                    $image = Image::make($fullPath);
                    
                    // Resize if too large (max 800x800 for avatars)
                    if ($image->width() > 800 || $image->height() > 800) {
                        $image->resize(800, 800, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }
                    
                    // Optimize quality
                    $image->save($fullPath, 85);
                    
                    Log::info('Avatar optimized successfully', [
                        'user_id' => $user->id,
                        'filename' => $filename,
                        'original_size' => $file->getSize(),
                        'optimized_size' => filesize($fullPath)
                    ]);
                } catch (Exception $e) {
                    Log::warning('Avatar optimization failed, using original', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return $relativePath;

        } catch (Exception $e) {
            Log::error('Avatar upload failed with exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
