<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiting\Limit;
use Symfony\Component\HttpFoundation\Response;

class LoginRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts())) {
            $this->logRateLimitExceeded($request);

            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        if ($response->getStatusCode() === 422) {
            RateLimiter::hit($key, $this->decayMinutes() * 60);
        }

        return $response;
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $identifier = $request->input('login') ?? $request->ip();
        return sha1($identifier . '|' . $request->ip());
    }

    /**
     * Get the maximum number of attempts for the given key.
     */
    protected function maxAttempts(): int
    {
        return config('auth.login_throttle_attempts', 5);
    }

    /**
     * Get the number of minutes to throttle for.
     */
    protected function decayMinutes(): int
    {
        return config('auth.login_throttle_decay_minutes', 1);
    }

    /**
     * Log rate limit exceeded attempts
     */
    protected function logRateLimitExceeded(Request $request): void
    {
        Log::warning('Login rate limit exceeded', [
            'ip_address' => $request->ip(),
            'login_attempt' => $request->input('login'),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
