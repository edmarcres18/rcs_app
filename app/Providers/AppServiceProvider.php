<?php

namespace App\Providers;

use App\Models\PendingUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\WebPushChannel;
use App\Notifications\Channels\TelegramChannel;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->roles === \App\Enums\UserRole::SYSTEM_ADMIN) {
                $pendingUpdatesCount = PendingUpdate::where('status', 'pending')->count();
                $view->with('pendingUpdatesCount', $pendingUpdatesCount);
            } else {
                $view->with('pendingUpdatesCount', 0);
            }
        });

        // Register custom notification channel
        Notification::extend('custom-webpush', function ($app) {
            return new WebPushChannel();
        });

        Notification::extend('telegram', function ($app) {
            return $app->make(TelegramChannel::class);
        });

        // Auto-setup Telegram environment if needed
        $this->setupTelegramEnvironment();
    }

    /**
     * Set up Telegram environment based on current app environment.
     *
     * @return void
     */
    protected function setupTelegramEnvironment()
    {
        // Only run this setup during HTTP requests, not during console commands
        if (app()->runningInConsole()) {
            return;
        }

        // Never perform external checks during Telegram webhook delivery to keep responses fast
        // and avoid upstream gateway timeouts
        try {
            if (request()->is('api/telegram/webhook')) {
                return;
            }
        } catch (\Throwable $e) {
            // If request() is not available for any reason, just proceed safely
        }

        // Get Telegram service (be resilient if token/config is missing)
        try {
            $telegramService = app(TelegramService::class);
        } catch (\Throwable $e) {
            Log::warning('Skipping Telegram environment setup due to configuration error', [
                'error' => $e->getMessage(),
            ]);
            return;
        }

        // If we're in a production environment
        if (app()->environment('production')) {
            // Avoid calling Telegram on every request: check at most once per 30 minutes
            $lastCheckedAt = Cache::get('telegram:webhook:last_checked_at');
            if ($lastCheckedAt && now()->diffInMinutes($lastCheckedAt) < 30) {
                return;
            }

            Cache::put('telegram:webhook:last_checked_at', now(), now()->addHours(1));

            $webhookUrl = rtrim(config('app.url'), '/') . '/api/telegram/webhook';
            $currentWebhook = $telegramService->getWebhookInfo();

            if (!$currentWebhook || !isset($currentWebhook['result']['url']) || $currentWebhook['result']['url'] !== $webhookUrl) {
                Log::info('Ensuring Telegram webhook is configured for production', ['expected_url' => $webhookUrl, 'current' => $currentWebhook['result']['url'] ?? null]);
                $telegramService->setWebhook($webhookUrl);
            }
        }
        // If we're in local environment
        elseif (app()->environment('local')) {
            // Check if we should use polling in local environment
            if (config('telegram.local.use_polling', true)) {
                // Make sure webhook is not set
                $webhookInfo = $telegramService->getWebhookInfo();
                if ($webhookInfo && !empty($webhookInfo['result']['url'])) {
                    Log::info('Deleting Telegram webhook for local development polling');
                    $telegramService->deleteWebhook();
                }
            }
        }
    }
}
