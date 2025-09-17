<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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
                $avatarResult = $this->handleAvatarUpload($request, $user, $validated);

                if (!$avatarResult['success']) {
                    return back()->withErrors(['avatar' => $avatarResult['message']]);
                }

                $validated['avatar'] = $avatarResult['path'];
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

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'An error occurred while updating your profile. Please try again.']);
        }
    }

    /**
     * Handle avatar upload with optimization and error handling.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @param  \App\Models\User  $user
     * @param  array  $validated
     * @return array
     */
    private function handleAvatarUpload(ProfileUpdateRequest $request, User $user, array &$validated): array
    {
        try {
            $avatar = $request->file('avatar');

            // Validate file size (double check)
            if ($avatar->getSize() > 10 * 1024 * 1024) { // 10MB
                return [
                    'success' => false,
                    'message' => 'The avatar file is too large. Maximum size is 10MB.'
                ];
            }

            // Validate image dimensions
            $imageInfo = getimagesize($avatar->getPathname());
            if (!$imageInfo) {
                return [
                    'success' => false,
                    'message' => 'The uploaded file is not a valid image.'
                ];
            }

            // Check image dimensions (max 4000x4000)
            if ($imageInfo[0] > 4000 || $imageInfo[1] > 4000) {
                return [
                    'success' => false,
                    'message' => 'Image dimensions are too large. Maximum size is 4000x4000 pixels.'
                ];
            }

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                try {
                    unlink(public_path($user->avatar));
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old avatar: ' . $e->getMessage());
                }
            }

            // Ensure upload directory exists
            $uploadDir = public_path('uploads/avatars');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $filename = time() . '_' . Str::slug($validated['first_name']) . '_' . Str::random(8) . '.jpg';
            $filePath = $uploadDir . '/' . $filename;

            // Process and optimize image
            $this->optimizeImage($avatar->getPathname(), $filePath);

            // Verify the file was created successfully
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to save the avatar. Please try again.'
                ];
            }

            return [
                'success' => true,
                'path' => 'uploads/avatars/' . $filename
            ];

        } catch (\Exception $e) {
            Log::error('Avatar upload failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'file_size' => $avatar->getSize() ?? 'unknown',
                'error' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while uploading the avatar. Please try again.'
            ];
        }
    }

    /**
     * Optimize and resize image for avatar.
     *
     * @param  string  $sourcePath
     * @param  string  $destinationPath
     * @return void
     */
    private function optimizeImage(string $sourcePath, string $destinationPath): void
    {
        try {
            // Use Intervention Image if available, otherwise fallback to GD
            if (class_exists('Intervention\Image\Facades\Image')) {
                Image::make($sourcePath)
                    ->resize(500, 500, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 85)
                    ->save($destinationPath);
            } else {
                // Fallback to GD functions
                $this->optimizeImageWithGD($sourcePath, $destinationPath);
            }
        } catch (\Exception $e) {
            Log::warning('Image optimization failed, using original: ' . $e->getMessage());
            // Fallback: just copy the original file
            copy($sourcePath, $destinationPath);
        }
    }

    /**
     * Optimize image using GD functions as fallback.
     *
     * @param  string  $sourcePath
     * @param  string  $destinationPath
     * @return void
     */
    private function optimizeImageWithGD(string $sourcePath, string $destinationPath): void
    {
        $imageInfo = getimagesize($sourcePath);
        $sourceImage = null;

        // Create image resource based on type
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        if (!$sourceImage) {
            throw new \Exception('Failed to create image resource');
        }

        // Calculate new dimensions (max 500x500, maintain aspect ratio)
        $maxSize = 500;
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width > $maxSize || $height > $maxSize) {
            $ratio = min($maxSize / $width, $maxSize / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($imageInfo[2] == IMAGETYPE_PNG || $imageInfo[2] == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save as JPEG with 85% quality
        imagejpeg($newImage, $destinationPath, 85);

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
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
