<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScalableAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    protected $authService;

    public function __construct(ScalableAuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get current user's active sessions
     */
    public function getSessions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $sessions = $this->authService->getUserSessions($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'sessions' => $sessions,
                    'total_sessions' => count($sessions),
                    'max_concurrent' => ScalableAuthService::MAX_CONCURRENT_SESSIONS
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sessions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $sessionId = $request->input('session_id');

            // Check if session belongs to current user
            $sessions = $this->authService->getUserSessions($user);
            $sessionExists = collect($sessions)->contains('id', $sessionId);

            if (!$sessionExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found or access denied'
                ], 404);
            }

            $result = $this->authService->destroySession($user, $sessionId);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Session terminated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate all sessions except current
     */
    public function terminateOtherSessions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();
            $sessions = $this->authService->getUserSessions($user);

            $terminatedCount = 0;
            foreach ($sessions as $session) {
                if ($session['id'] !== $currentSessionId) {
                    if ($this->authService->destroySession($user, $session['id'])) {
                        $terminatedCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Terminated {$terminatedCount} other sessions",
                'data' => [
                    'terminated_count' => $terminatedCount,
                    'remaining_sessions' => $this->authService->getUserSessions($user)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate other sessions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get session statistics (admin only)
     */
    public function getSessionStats(Request $request): JsonResponse
    {
        try {
            // Check if user has admin privileges
            if (!Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ], 403);
            }

            $stats = $this->authService->getSessionStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force logout user from all devices (admin only)
     */
    public function forceLogoutUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if current user has admin privileges
            if (!Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ], 403);
            }

            $targetUser = \App\Models\User::findOrFail($request->input('user_id'));
            $result = $this->authService->forceLogoutAll($targetUser);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "User {$targetUser->name} has been logged out from all devices"
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to force logout user'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to force logout user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
