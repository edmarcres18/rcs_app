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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

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

        if (App::environment('production') && config('telegram.api_token')) {
            try {
                // Get current webhook info
                Artisan::call('telegram:webhook-info');
                $output = Artisan::output();

                $expectedUrl = config('telegram.webhook_url');

                // Check if current webhook URL matches expected
                if (!str_contains($output, $expectedUrl)) {
                    Artisan::call('telegram:setup-webhook');
                    Log::info('✅ Telegram webhook was set automatically: ' . $expectedUrl);
                } else {
                    Log::info('ℹ️ Telegram webhook already set to: ' . $expectedUrl);
                }
            } catch (\Exception $e) {
                Log::error('❌ Telegram webhook setup failed: ' . $e->getMessage());
            }
        }
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

        // Get Telegram service
        $telegramService = app(TelegramService::class);

        // If we're in local environment
        if (app()->environment('local')) {
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
