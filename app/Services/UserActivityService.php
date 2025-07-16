<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class UserActivityService
{
    /**
     * Log user activity
     *
     * @param string $activityType
     * @param string $activityDescription
     * @param array $details
     * @param User|null $user
     * @return UserActivity
     */
    public static function log(string $activityType, string $activityDescription, array $details = [], User $user = null)
    {
        $user = $user ?: Auth::user();

        if (!$user) {
            return null;
        }

        $request = request();
        $ipAddress = self::getIpAddress($request);

        // Get device information using Agent package
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $device = $agent->device();
        $browser = $agent->browser();
        $platform = $agent->platform();

        // Get location information using IP address
        $location = null;
        $locationData = Location::get($ipAddress);

        if ($locationData) {
            $location = $locationData->cityName . ', ' . $locationData->regionName . ', ' . $locationData->countryName;
        }

        return UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'activity_description' => $activityDescription,
            'details' => $details,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'device' => $device ?: 'Unknown',
            'browser' => $browser ?: 'Unknown',
            'platform' => $platform ?: 'Unknown',
            'location' => $location,
        ]);
    }

    /**
     * Get client IP address
     *
     * @param Request $request
     * @return string
     */
    public static function getIpAddress(Request $request)
    {
        $ipAddress = $request->ip();

        // Check for proxy IP addresses
        if ($request->header('X-Forwarded-For')) {
            $ipAddresses = explode(',', $request->header('X-Forwarded-For'));
            $ipAddress = trim(end($ipAddresses));
        }

        return $ipAddress;
    }

    /**
     * Log login activity
     *
     * @param User $user
     * @return UserActivity
     */
    public static function logLogin(User $user)
    {
        return self::log(
            'login',
            'User logged in',
            [],
            $user
        );
    }

    /**
     * Log logout activity
     *
     * @param User $user
     * @return UserActivity
     */
    public static function logLogout(User $user)
    {
        return self::log(
            'logout',
            'User logged out',
            [],
            $user
        );
    }

    /**
     * Log user creation activity
     *
     * @param User $user
     * @param User $createdUser
     * @return UserActivity
     */
    public static function logUserCreated(User $user, User $createdUser)
    {
        return self::log(
            'user_created',
            'Created a new user: ' . $createdUser->first_name . ' ' . $createdUser->last_name,
            [
                'created_user_id' => $createdUser->id,
                'email' => $createdUser->email,
                'roles' => $createdUser->roles->value,
            ],
            $user
        );
    }

    /**
     * Log user update activity
     *
     * @param User $user
     * @param User $updatedUser
     * @param array $oldData
     * @return UserActivity
     */
    public static function logUserUpdated(User $user, User $updatedUser, array $oldData = [])
    {
        $changes = [];

        foreach ($updatedUser->getChanges() as $field => $newValue) {
            if ($field !== 'updated_at' && $field !== 'password' && isset($oldData[$field])) {
                $changes[$field] = [
                    'old' => $oldData[$field],
                    'new' => $newValue,
                ];
            }
        }

        return self::log(
            'user_updated',
            'Updated user: ' . $updatedUser->first_name . ' ' . $updatedUser->last_name,
            [
                'updated_user_id' => $updatedUser->id,
                'email' => $updatedUser->email,
                'changes' => $changes,
            ],
            $user
        );
    }

    /**
     * Log user deletion activity
     *
     * @param User $user
     * @param User $deletedUser
     * @return UserActivity
     */
    public static function logUserDeleted(User $user, User $deletedUser)
    {
        return self::log(
            'user_deleted',
            'Deleted user: ' . $deletedUser->first_name . ' ' . $deletedUser->last_name,
            [
                'deleted_user_id' => $deletedUser->id,
                'email' => $deletedUser->email,
                'roles' => $deletedUser->roles->value,
            ],
            $user
        );
    }

    /**
     * Log password reset activity
     *
     * @param User $user
     * @return UserActivity
     */
    public static function logPasswordReset(User $user)
    {
        return self::log(
            'password_reset',
            'Password was reset',
            [],
            $user
        );
    }

    /**
     * Log failed login attempt
     *
     * @param string $email
     * @return UserActivity|null
     */
    public static function logFailedLogin(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Log the attempt but don't associate it with a user
            $request = request();
            $ipAddress = self::getIpAddress($request);

            return UserActivity::create([
                'activity_type' => 'failed_login',
                'activity_description' => 'Failed login attempt for ' . $email,
                'details' => ['email' => $email],
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
            ]);
        }

        return self::log(
            'failed_login',
            'Failed login attempt',
            [],
            $user
        );
    }
}
