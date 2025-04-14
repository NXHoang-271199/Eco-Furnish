<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-reviews');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'desc');

        $products = Product::withReviewStats($sort)
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%$search%");
            })
            ->paginate(10);

        return view('admins.reviews.index', compact('products', 'search', 'sort'));
    }
    public function productReviews(Product $product, Request $request)
    {
        $reviews = Review::with(['user', 'product'])
            ->where('product_id', $product->id)
            ->when($request->search, function ($query, $search) {
                return $query->where('review_text', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admins.reviews.product_reviews', compact('product', 'reviews'));
    }

    public function show($id)
    {
        $review = Review::with(['user', 'product', 'order.orderItems.productVariant' => function ($query) {
            $query->withTrashed(); // Lấy cả product variant đã bị soft delete
        }])->findOrFail($id);

        // Giải mã hình ảnh nếu cần
        if (is_string($review->images)) {
            $review->images = json_decode($review->images, true);
        }

        // Tìm orderItem có cùng product_id với review
        $matchingItem = $review->order?->orderItems
            ->firstWhere('product_id', $review->product_id);

        $variantInfo = [];

        if ($matchingItem && $matchingItem->productVariant && !empty($matchingItem->productVariant->variant_details)) {
            foreach ($matchingItem->productVariant->variant_details as $detail) {
                $variantInfo[] = "{$detail['name']}: {$detail['value']}";
            }
        }

        // Gắn vào review để dùng trong view
        $review->setAttribute('variant_info', $variantInfo);

        return view('admins.reviews.show', compact('review'));
    }


    public function toggleReviewVisibility($reviewId)
    {
        $review = Review::findOrFail($reviewId);
        $review->update(['is_hidden' => !$review->is_hidden]);

        return back()->with('success', $review->is_hidden ? 'Đánh giá đã bị ẩn' : 'Đánh giá đã hiển thị lại');
    }
    public function userInfo(User $user)
    {
        // Lấy tổng số đánh giá của người dùng
        $reviewCount = Review::where('user_id', $user->id)->count();  // Giả sử Review là model lưu thông tin đánh giá

        // Lấy tổng số đơn hàng của người dùng
        $orderCount = Order::where('user_id', $user->id)->count(); // Hoặc $user->orders->count() nếu bạn đã định nghĩa quan hệ orders

        return view('admins.reviews.user_info', compact('user', 'reviewCount', 'orderCount'));
    }
}
