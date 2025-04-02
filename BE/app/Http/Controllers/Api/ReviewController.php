<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Tạo đánh giá mới kèm hình ảnh
     */
    public function store(ReviewRequest $request)
    {
        $userId = Auth::id();
        $productId = $request->product_id;

        // Kiểm tra xem người dùng có đơn hàng chứa sản phẩm không
        $order = Order::where('user_id', $userId)
            ->whereHas('orderItems', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->orderBy('created_at', 'desc') // Lấy đơn mới nhất
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa mua sản phẩm này.'
            ], 403);
        }

        // Kiểm tra trạng thái đơn hàng có đủ điều kiện để đánh giá không
        if (!in_array($order->order_status, ['Đã Nhận', 'Hoàn Hàng', 'Từ Chối Hoàn Hàng'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể đánh giá khi đơn hàng đã hoàn tất.'
            ], 403);
        }

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
        $existingReview = Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('order_id', $order->id)
            ->exists();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá sản phẩm này rồi.'
            ], 403);
        }

        // Xử lý upload hình ảnh nếu có
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }

        // Lưu đánh giá
        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'images' => json_encode($imagePaths),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công',
            'data' => $review
        ]);
    }
    /**
     * Lấy danh sách đánh giá của sản phẩm
     */
    public function getProductReviews($productId)
    {
        // Lấy đánh giá cho sản phẩm, chỉ lấy những đánh giá không bị ẩn
        $reviews = Review::with('user')
            ->where('product_id', $productId)
            ->where('is_hidden', false)  // Điều kiện để chỉ lấy đánh giá không bị ẩn
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                $review->images = json_decode($review->images, true); // Giải mã JSON
                return $review;
            });

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
