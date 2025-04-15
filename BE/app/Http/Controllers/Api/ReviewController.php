<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
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
        $variantId = $request->product_variant_id;
        $orderId = $request->order_id;

        // 1. Kiểm tra biến thể có thuộc sản phẩm không (nếu có gửi biến thể)
        if ($variantId) {
            $isValidVariant = ProductVariant::where('id', $variantId)
                ->where('product_id', $productId)
                ->exists();

            if (!$isValidVariant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể không hợp lệ với sản phẩm.'
                ], 403);
            }
        }

        $order = Order::where('user_id', $userId)
            ->where('id', $orderId)
            ->whereHas('orderItems', function ($query) use ($productId, $variantId) {
                $query->where('product_id', $productId);
                if ($variantId) {
                    $query->where('product_variant_id', $variantId);
                } else {
                    $query->whereNull('product_variant_id');
                }
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm này không có trong đơn hàng của bạn.'
            ], 403);
        }

        // Kiểm tra trạng thái đơn hàng
        if ($order->order_status === 'Hủy Đơn') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã bị hủy, không thể đánh giá.'
            ], 403);
        }

        if ($order->order_status === 'Hoàn Hàng') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã hoàn trả, không thể đánh giá.'
            ], 403);
        }

        if ($order->order_status !== 'Đã Nhận') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể đánh giá khi đơn hàng đã hoàn tất.'
            ], 403);
        }
        // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
        $existingReview = Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('order_id', $orderId)
            ->when($variantId, function ($query) use ($variantId) {
                $query->where('product_variant_id', $variantId);
            }, function ($query) {
                $query->whereNull('product_variant_id');
            })
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
            'product_variant_id' => $variantId,
            'order_id' => $orderId,
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
        $reviews = Review::with(['user', 'productVariant'])
            ->where('product_id', $productId)
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                $review->images = json_decode($review->images, true);

                // Gán thông tin biến thể đơn giản hơn
                $review->variant_info = $review->productVariant->variant_details ?? [];

                // Ẩn những thông tin không cần thiết
                unset(
                    $review->order_id,
                    $review->updated_at,
                    $review->productVariant,
                    $review->order
                );

                // Ẩn các thông tin nhạy cảm của user
                if ($review->user) {
                    unset(
                        $review->user->email_verified_at,
                        $review->user->email_verification_token,
                        $review->user->remember_token,
                        $review->user->remember_me,
                        $review->user->remember_me_expires_at,
                        $review->user->created_at,
                        $review->user->updated_at
                    );
                }

                return $review;
            });

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }


    /**
     * Kiểm tra xem người dùng có thể đánh giá sản phẩm hay không
     *
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function canReview($productId)
    {
        $userId = Auth::id();
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

        // Kiểm tra trạng thái đơn hàng có đủ điều kiện để đánh giá không
        if ($order->order_status !== 'Đã Nhận') {
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

        // Nếu tất cả các điều kiện đều thỏa mãn, người dùng có thể đánh giá
        return response()->json([
            'success' => true,
            'message' => 'Bạn có thể đánh giá sản phẩm này',
            'order_id' => $order->id
        ]);
    }
}
