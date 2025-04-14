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
        $reviews = Review::with([
            'user',
            'order.orderItems.productVariant' => function ($query) {
                $query->withTrashed();
            }
        ])
            ->where('product_id', $productId)
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                $review->images = json_decode($review->images, true);
                $orderItem = $review->order?->orderItems
                    ->firstWhere('product_id', $review->product_id);
                $variantInfo = [];
                if (
                    $orderItem &&
                    $orderItem->productVariant &&
                    is_array($orderItem->productVariant->variant_details)
                ) {
                    $variantInfo = collect($orderItem->productVariant->variant_details)
                        ->map(fn($detail) => "{$detail['name']}: {$detail['value']}")
                        ->toArray();
                }
                $review->variant_info = $variantInfo;
                unset($review->order);
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

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đánh giá sản phẩm'
            ], 401);
        }

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
