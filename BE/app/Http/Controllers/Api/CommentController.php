<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string|max:500',
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        // Kiểm tra xem người dùng có đơn hàng chứa sản phẩm không
        $order = Order::where('user_id', $userId)
            ->whereHas('orderItems', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->whereNotIn('order_status', ['Hoàn Hàng', 'Hủy Đơn'])
            ->orderBy('created_at', 'desc') // Lấy đơn mới nhất
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa mua sản phẩm này.'
            ], 403);
        }

        // Kiểm tra trạng thái đơn hàng có đủ điều kiện để bình luận không
        if ($order->order_status !== 'Đã Nhận') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể bình luận khi đơn hàng đã hoàn tất.'
            ], 403);
        }

        $comment = Comment::create([
            'product_id' => $request->product_id,
            'user_id' => $userId,
            'content' => $request->content,
            'status' => 'Hiển thị', // Mặc định hiển thị, admin có thể ẩn sau
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bình luận đã được thêm thành công',
            'data' => $comment->load('user')
        ]);
    }

    /**
     * Get comments for a product.
     */
    public function getProductComments($productId)
    {
        $comments = Comment::with('user')
            ->where('product_id', $productId)
            ->where('status', 'Hiển thị')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }
}
