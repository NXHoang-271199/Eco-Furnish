<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderNotification;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 10);

        // Lấy các đơn hàng của người dùng
        $userOrderIds = Order::where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        // Lấy thông báo liên quan đến đơn hàng của người dùng
        $notifications = OrderNotification::whereIn('order_id', $userOrderIds)
            ->with('order') // Eager load order
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Xử lý và làm giàu thông tin thông báo
        $processedNotifications = $notifications->map(function ($notification) {
            // Lấy thông tin đơn hàng liên quan
            $order = $notification->order;
            
            // Gán các trường cần thiết từ đơn hàng vào thông báo
            if ($order) {
                $notification->order_code = $order->order_code;
                $notification->order_status = $order->order_status;
                $notification->message = $notification->message ?: "Đơn hàng #{$order->order_code} đã chuyển sang trạng thái: {$order->order_status}";
            } else {
                $notification->order_code = "Không xác định";
                $notification->order_status = "Không xác định";
                $notification->message = $notification->message ?: "Thông báo về đơn hàng";
            }
            
            return $notification;
        });

        // Đếm số thông báo chưa đọc
        $unreadCount = OrderNotification::whereIn('order_id', $userOrderIds)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Đánh dấu một thông báo là đã đọc
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        // Tìm thông báo
        $notification = OrderNotification::findOrFail($id);
        
        // Kiểm tra xem thông báo có thuộc về đơn hàng của người dùng này không
        $order = Order::where('id', $notification->order_id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập thông báo này'
            ], 403);
        }
        
        // Đánh dấu là đã đọc
        $notification->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Thông báo đã được đánh dấu là đã đọc',
            'notification' => $notification
        ]);
    }
    
    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        // Lấy các đơn hàng của người dùng
        $userOrderIds = Order::where('user_id', $user->id)
            ->pluck('id')
            ->toArray();
            
        // Đánh dấu tất cả thông báo liên quan đến đơn hàng của người dùng là đã đọc
        OrderNotification::whereIn('order_id', $userOrderIds)
            ->update(['is_read' => true]);
            
        return response()->json([
            'success' => true,
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc'
        ]);
    }
} 