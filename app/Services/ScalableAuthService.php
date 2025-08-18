<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScalableAuthService
{
    /**
     * Maximum concurrent sessions per user
     */
    const MAX_CONCURRENT_SESSIONS = 5;

    /**
     * Session timeout in minutes
     */
    const SESSION_TIMEOUT = 120;

    /**
     * Create a new session for user
     */
    public function createSession(User $user, string $sessionId): array
    {
        try {
            // Check concurrent session limit
            if (!$this->canCreateNewSession($user)) {
                $this->cleanupOldSessions($user);

                if (!$this->canCreateNewSession($user)) {
                    throw new \Exception('Maximum concurrent sessions reached. Please logout from another device.');
                }
            }

            // Generate session data
            $sessionData = [
                'id' => $sessionId,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()->toISOString(),
                'last_activity' => now()->toISOString(),
                'device_info' => $this->getDeviceInfo(),
            ];

            // Store session in Redis
            $this->storeSession($user, $sessionId, $sessionData);

            // Log session creation
            Log::info('Session created', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'ip_address' => $sessionData['ip_address']
            ]);

            return $sessionData;
        } catch (\Exception $e) {
            Log::error('Session creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update session activity
     */
    public function updateSessionActivity(User $user, string $sessionId): bool
    {
        try {
            $sessionKey = "user_session:{$user->id}:{$sessionId}";
            $sessionData = Redis::get($sessionKey);

            if (!$sessionData) {
                return false;
            }

            $session = json_decode($sessionData, true);
            $session['last_activity'] = now()->toISOString();

            Redis::setex($sessionKey, self::SESSION_TIMEOUT * 60, json_encode($session));

            return true;
        } catch (\Exception $e) {
            Log::error('Session activity update failed', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Destroy session
     */
    public function destroySession(User $user, string $sessionId): bool
    {
        try {
            $sessionKey = "user_session:{$user->id}:{$sessionId}";
            Redis::del($sessionKey);

            // Remove from user sessions set
            Redis::srem("user_sessions:{$user->id}", $sessionId);

            Log::info('Session destroyed', [
                'user_id' => $user->id,
                'session_id' => $sessionId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Session destruction failed', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all active sessions for user
     */
    public function getUserSessions(User $user): array
    {
        try {
            $sessionIds = Redis::smembers("user_sessions:{$user->id}");
            $sessions = [];

            foreach ($sessionIds as $sessionId) {
                $sessionKey = "user_session:{$user->id}:{$sessionId}";
                $sessionData = Redis::get($sessionKey);

                if ($sessionData) {
                    $sessions[] = json_decode($sessionData, true);
                }
            }

            return $sessions;
        } catch (\Exception $e) {
            Log::error('Failed to get user sessions', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Force logout from all devices
     */
    public function forceLogoutAll(User $user): bool
    {
        try {
            $sessionIds = Redis::smembers("user_sessions:{$user->id}");

            foreach ($sessionIds as $sessionId) {
                $this->destroySession($user, $sessionId);
            }

            Log::info('All sessions destroyed for user', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to destroy all sessions', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if user can create new session
     */
    private function canCreateNewSession(User $user): bool
    {
        $activeSessions = Redis::scard("user_sessions:{$user->id}");
        return $activeSessions < self::MAX_CONCURRENT_SESSIONS;
    }

    /**
     * Store session in Redis
     */
    private function storeSession(User $user, string $sessionId, array $sessionData): void
    {
        $sessionKey = "user_session:{$user->id}:{$sessionId}";

        // Store session data
        Redis::setex($sessionKey, self::SESSION_TIMEOUT * 60, json_encode($sessionData));

        // Add to user sessions set
        Redis::sadd("user_sessions:{$user->id}", $sessionId);

        // Set expiration for user sessions set
        Redis::expire("user_sessions:{$user->id}", self::SESSION_TIMEOUT * 60);
    }

    /**
     * Cleanup old sessions
     */
    private function cleanupOldSessions(User $user): void
    {
        $sessionIds = Redis::smembers("user_sessions:{$user->id}");

        foreach ($sessionIds as $sessionId) {
            $sessionKey = "user_session:{$user->id}:{$sessionId}";
            $sessionData = Redis::get($sessionKey);

            if (!$sessionData) {
                Redis::srem("user_sessions:{$user->id}", $sessionId);
                continue;
            }

            $session = json_decode($sessionData, true);
            $lastActivity = Carbon::parse($session['last_activity']);

            if ($lastActivity->diffInMinutes(now()) > self::SESSION_TIMEOUT) {
                $this->destroySession($user, $sessionId);
            }
        }
    }

    /**
     * Get device information
     */
    private function getDeviceInfo(): array
    {
        $userAgent = request()->userAgent();

        return [
            'browser' => $this->detectBrowser($userAgent),
            'os' => $this->detectOS($userAgent),
            'device' => $this->detectDevice($userAgent),
            'ip_address' => request()->ip(),
        ];
    }

    /**
     * Detect browser from user agent
     */
    private function detectBrowser(string $userAgent): string
    {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';

        return 'Unknown';
    }

    /**
     * Detect OS from user agent
     */
    private function detectOS(string $userAgent): string
    {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false) return 'iOS';

        return 'Unknown';
    }

    /**
     * Detect device type from user agent
     */
    private function detectDevice(string $userAgent): string
    {
        if (strpos($userAgent, 'Mobile') !== false) return 'Mobile';
        if (strpos($userAgent, 'Tablet') !== false) return 'Tablet';

        return 'Desktop';
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        try {
            $totalUsers = User::count();
            $activeSessions = 0;
            $totalSessions = 0;

            // Get all user session sets
            $keys = Redis::keys('user_sessions:*');

            foreach ($keys as $key) {
                $sessionCount = Redis::scard($key);
                $activeSessions += $sessionCount;
                $totalSessions += $sessionCount;
            }

            return [
                'total_users' => $totalUsers,
                'active_sessions' => $activeSessions,
                'total_sessions' => $totalSessions,
                'redis_memory_usage' => Redis::info('memory')['used_memory_human'] ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get session stats', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
