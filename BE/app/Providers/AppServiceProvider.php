<?php

namespace App\Providers;

use App\Models\OrderNotification;
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
        $unreadCount = OrderNotification::where('is_read', false)->count();
        View::share('unreadCount', $unreadCount);
    }
}
