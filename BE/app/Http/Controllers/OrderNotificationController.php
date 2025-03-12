<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderNotification;

class OrderNotificationController extends Controller
{
    public function index()
    {
        $notifications = OrderNotification::with('order')
            ->orderBy('created_at', 'desc')
            ->get();
            $notificationsCount = $notifications->count();
        // Tính số lượng thông báo chưa đọc
        $unreadCount = OrderNotification::where('is_read', false)->count();
        return view('admins.orders.notification', compact('notifications','unreadCount','notificationsCount'));
    }

    public function markAsRead($id)
    {
        $notification = OrderNotification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Thông báo đã được đánh dấu là đã đọc.');
    }
}
