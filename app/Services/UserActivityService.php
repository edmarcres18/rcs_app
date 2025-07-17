<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Log;

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

        $activity = UserActivity::create([
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

        if ($activity) {
            self::sendTelegramNotification($activity);
        }

        return $activity;
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
     * Format user activity for Telegram notification.
     *
     * @param UserActivity $activity
     * @return string
     */
    private static function formatActivityForTelegram(UserActivity $activity): string
    {
        $user = $activity->user;
        $userName = 'System/Unknown';
        $userEmail = 'N/A';

        if ($user) {
            $userName = e($user->full_name);
            $userEmail = e($user->email);
        } elseif (isset($activity->details['email'])) {
            $userName = 'Unknown User';
            $userEmail = e($activity->details['email']);
        }

        $message = "<b>📢 User Activity Notification</b>\n\n";
        $message .= "A new activity has been logged:\n\n";
        $message .= "<b>👤 User:</b> " . $userName . " (" . $userEmail . ")\n";
        $message .= "<b>✨ Activity:</b> " . e(ucwords(str_replace('_', ' ', $activity->activity_type))) . "\n";
        $message .= "<b>📝 Description:</b> " . e($activity->activity_description) . "\n";
        $message .= "<b>🗓️ Time:</b> " . $activity->created_at->format('Y-m-d H:i:s T') . "\n";
        $message .= "<b>📍 IP Address:</b> " . e($activity->ip_address) . "\n";

        if ($activity->location) {
            $message .= "<b>🌍 Location:</b> " . e($activity->location) . "\n";
        }

        if (!empty($activity->details)) {
            $message .= "<b>🔍 Details:</b>\n";
            $message .= "<code>" . e(json_encode($activity->details, JSON_PRETTY_PRINT)) . "</code>";
        }

        return $message;
    }

    /**
     * Send Telegram notification for user activity to system admins.
     *
     * This is sent in real-time and does not use the queue.
     *
     * @param UserActivity $activity
     * @return void
     */
    public static function sendTelegramNotification(UserActivity $activity)
    {
        // Notify only system admins
        $admins = User::where('roles', UserRole::SYSTEM_ADMIN)->get();
        if ($admins->isEmpty()) {
            return;
        }

        $message = self::formatActivityForTelegram($activity);

        $telegramService = app(TelegramService::class);

        foreach ($admins as $admin) {
            // Check if the admin has a Telegram Chat ID and notifications enabled
            if ($admin->telegram_chat_id && $admin->telegram_notifications_enabled) {
                try {
                    $telegramService->sendMessage($admin->telegram_chat_id, $message, ['parse_mode' => 'HTML']);
                } catch (\Exception $e) {
                    // Log error but don't break the process
                    Log::error('Failed to send Telegram activity notification', [
                        'user_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
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

            $activity = UserActivity::create([
                'activity_type' => 'failed_login',
                'activity_description' => 'Failed login attempt for ' . $email,
                'details' => ['email' => $email],
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
            ]);

            if ($activity) {
                self::sendTelegramNotification($activity);
            }
            return $activity;
        }

        return self::log(
            'failed_login',
            'Failed login attempt',
            [],
            $user
        );
    }
}
