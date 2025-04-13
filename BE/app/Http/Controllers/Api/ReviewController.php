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
        $orderId = $request->order_id; // Lấy order_id từ request

        // Kiểm tra xem order_id có được cung cấp không
        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin mã đơn hàng.'
            ], 400); // Bad Request
        }

        // Lấy đơn hàng cụ thể dựa trên order_id và user_id
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->whereHas('orderItems', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->first();

        // Kiểm tra xem đơn hàng có tồn tại, thuộc về người dùng và chứa sản phẩm không
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng hợp lệ chứa sản phẩm này.'
            ], 404); // Not Found or Forbidden
        }

        // Kiểm tra trạng thái đơn hàng có đủ điều kiện để đánh giá không
        if ($order->order_status !== 'Đã Nhận') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể đánh giá khi đơn hàng đã hoàn tất.'
            ], 403);
        }

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này cho đơn hàng này chưa
        $existingReview = Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('order_id', $order->id)
            ->exists();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá sản phẩm này cho đơn hàng này rồi.' // Cập nhật thông báo lỗi
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

        // Lưu đánh giá với order_id từ request
        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $order->id, // Sử dụng order_id đã xác thực
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
        $reviews = Review::with([
                'user', 
                'product', 
                'order.orderItems' => function($query) use ($productId) {
                    // Chỉ lấy orderItem của sản phẩm được đánh giá
                    $query->where('product_id', $productId);
                }
            ])
            ->where('product_id', $productId)
            ->where('is_hidden', false)  // Điều kiện để chỉ lấy đánh giá không bị ẩn
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) use ($productId) {
                $review->images = json_decode($review->images, true); // Giải mã JSON
                
                // Thêm thông tin sản phẩm và biến thể từ orderItem
                if ($review->order && $review->order->orderItems->isNotEmpty()) {
                    $orderItem = $review->order->orderItems->first();
                    $review->product_name = $orderItem->product_name;
                    $review->product_image = $orderItem->image_url;
                    
                    // Thêm thông tin biến thể nếu có
                    if ($orderItem->product_variant_id) {
                        $review->has_variant = true;
                        
                        // Lấy thông tin biến thể
                        $productVariant = \App\Models\ProductVariant::withTrashed()
                            ->where('id', $orderItem->product_variant_id)
                            ->first();
                            
                        if ($productVariant && !empty($productVariant->variant_details)) {
                            $review->variant_details = $productVariant->variant_details;
                        }
                    } else {
                        $review->has_variant = false;
                    }
                } else {
                    // Fallback nếu không tìm thấy orderItem
                    $review->product_name = $review->product ? $review->product->name : 'Sản phẩm không xác định';
                    $review->product_image = $review->product ? $review->product->image_thumnail : null;
                    $review->has_variant = false;
                }
                
                // Loại bỏ các dữ liệu lớn không cần thiết
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

    /**
     * Lấy danh sách đánh giá của một đơn hàng cụ thể
     * 
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderReviews($orderId)
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để xem đánh giá'
            ], 401);
        }

        // Kiểm tra đơn hàng có thuộc về người dùng không
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        // Lấy danh sách đánh giá của đơn hàng
        $reviews = Review::with(['product'])
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                $review->images = json_decode($review->images, true); // Giải mã JSON
                
                // Lấy thông tin về orderItem để lấy tên sản phẩm và thông tin biến thể
                $orderItem = \App\Models\OrderItem::where('order_id', $review->order_id)
                    ->where('product_id', $review->product_id)
                    ->first();
                
                if ($orderItem) {
                    $review->product_name = $orderItem->product_name;
                    $review->product_image = $orderItem->image_url;
                    
                    // Thêm thông tin biến thể nếu có
                    if ($orderItem->product_variant_id) {
                        $review->has_variant = true;
                        
                        // Lấy thông tin biến thể
                        $productVariant = \App\Models\ProductVariant::withTrashed()
                            ->where('id', $orderItem->product_variant_id)
                            ->first();
                            
                        if ($productVariant && !empty($productVariant->variant_details)) {
                            $review->variant_details = $productVariant->variant_details;
                        }
                    } else {
                        $review->has_variant = false;
                    }
                } else {
                    // Fallback nếu không tìm thấy orderItem
                    $review->product_name = $review->product ? $review->product->name : 'Sản phẩm không xác định';
                    $review->product_image = $review->product ? $review->product->image_thumnail : null;
                    $review->has_variant = false;
                }
                
                // Loại bỏ các dữ liệu lớn không cần thiết
                unset($review->product);
                
                return $review;
            });

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }
}
