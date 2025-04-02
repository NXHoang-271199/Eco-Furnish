<?php

namespace App\Providers;

use App\Models\OrderNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
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
        // Kiểm tra nếu bảng tồn tại trước khi truy vấn
        $unreadCount = 0;

        if (Schema::hasTable('order_notifications')) {
            $unreadCount = OrderNotification::where('is_read', false)->count();
        }

        View::share('unreadCount', $unreadCount);
    }
}
