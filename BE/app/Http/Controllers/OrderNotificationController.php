<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderNotification;
use Illuminate\Support\Facades\DB;

class OrderNotificationController extends Controller
{
    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-order-notifications');
        $this->middleware('permission:update-order-notifications', ['only' => ['markAsRead']]);
    }

    public function index()
    {
        // Lấy các ID của thông báo mới nhất cho mỗi đơn hàng
        $latestNotificationIds = OrderNotification::select('order_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('order_id')
            ->orderBy('max_id', 'desc')
            ->get()
            ->pluck('max_id');
        
        // Lấy thông báo đầy đủ dựa trên các ID mới nhất
        $notifications = OrderNotification::with('order.user')
            ->whereIn('id', $latestNotificationIds)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $notificationsCount = $latestNotificationIds->count();
        
        // Tính số lượng đơn hàng có thông báo chưa đọc
        $unreadCount = OrderNotification::whereIn('id', $latestNotificationIds)
            ->where('is_read', false)
            ->count();
            
        return view('admins.orders.notification', compact('notifications', 'unreadCount', 'notificationsCount'));
    }

    public function markAsRead($id)
    {
        $notification = OrderNotification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Thông báo đã được đánh dấu là đã đọc.');
    }
}
