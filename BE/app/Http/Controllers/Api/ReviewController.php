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

        // Nếu không có order_id, trả về thông báo yêu cầu người dùng phải mua sản phẩm
        if (!$request->order_id) {
            return response()->json(['success' => false, 'message' => 'Bạn phải mua sản phẩm này trước khi đánh giá'], 403);
        }

        // Kiểm tra xem đơn hàng có tồn tại và có trạng thái "Đã Nhận" hoặc "Hoàn Hàng"
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ'], 404);
        }

        if (!in_array($order->order_status, ['Đã Nhận', 'Hoàn Hàng'])) {
            return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể đánh giá sản phẩm từ những đơn hàng đã hoàn tất'], 403);
        }

        // Kiểm tra xem người dùng đã mua sản phẩm trong đơn hàng này chưa
        $hasPurchased = $order->orderItems()->where('product_id', $request->product_id)->exists();

        if (!$hasPurchased) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa mua sản phẩm này trong đơn hàng nên không thể đánh giá'], 403);
        }

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này trong đơn hàng này chưa
        $existingReview = Review::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingReview) {
            return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể đánh giá sản phẩm này một lần trên mỗi đơn hàng'], 409);
        }

        // Xử lý upload hình ảnh
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Lưu ảnh vào thư mục reviews trong public disk
                $path = $image->store('reviews', 'public'); // Lưu vào storage/app/public/reviews
                $imagePaths[] = $path; // Lưu đường dẫn tương đối
            }
        }

        // Tạo đánh giá mới
        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'images' => json_encode($imagePaths), // Lưu ảnh dưới dạng JSON
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi',
            'data' => $review
        ]);
    }



    /**
     * Lấy danh sách đánh giá của sản phẩm
     */
    public function getProductReviews($productId)
    {
        $reviews = Review::with('user')
            ->where('product_id', $productId)
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
