<?php

namespace App\Providers;

use App\Models\PendingUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->roles === \App\Enums\UserRole::SYSTEM_ADMIN) {
                $pendingUpdatesCount = PendingUpdate::where('status', 'pending')->count();
                $view->with('pendingUpdatesCount', $pendingUpdatesCount);
            } else {
                $view->with('pendingUpdatesCount', 0);
            }
        });
    }
}
