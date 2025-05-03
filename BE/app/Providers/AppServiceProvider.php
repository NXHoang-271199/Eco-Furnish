<?php

namespace App\Providers;

use App\Models\OrderNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

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
        Schema::defaultStringLength(191);

        // Kiểm tra nếu bảng tồn tại trước khi truy vấn
        $unreadCount = 0;

        if (Schema::hasTable('order_notifications')) {
            // Lấy danh sách các ID thông báo mới nhất cho mỗi đơn hàng
            $latestNotificationIds = OrderNotification::select('order_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('order_id')
                ->pluck('max_id');
                
            // Đếm số lượng thông báo chưa đọc từ danh sách thông báo mới nhất
            $unreadCount = OrderNotification::whereIn('id', $latestNotificationIds)
                ->where('is_read', false)
                ->count();
        }

        View::share('unreadCount', $unreadCount);
    }
}
