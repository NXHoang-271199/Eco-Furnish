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
        // Lấy ID người dùng hiện tại
        $userId = Auth::id();  // Hoặc auth()->id()

        // Kiểm tra xem người dùng đã mua sản phẩm trong đơn hàng này chưa
        $hasPurchased = Order::where('id', $request->order_id)
            ->where('user_id', $userId)
            ->whereHas('orderItems', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa mua sản phẩm này nên không thể đánh giá'], 403);
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
                $path = $image->store('reviews', 'public'); // Lưu vào storage/app/public/reviews
                $imagePaths[] = Storage::url($path); // Lưu đường dẫn file
            }
        }

        // Tạo đánh giá mới
        $review = Review::create([
            'user_id' => $userId, // Sử dụng $userId thay vì Auth::user()->id
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
