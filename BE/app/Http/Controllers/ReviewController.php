<?php

namespace App\Http\Controllers;

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





    public function viewProductReviews($productId)
    {
        $productReviews = Review::with(['user', 'images', 'replies'])
            ->where('product_id', $productId)
            ->get();

        return view('admin.reviews.detail', compact('productReviews'));
    }
    public function replyReview(Request $request, $reviewId)
    {
        $request->validate(['reply_text' => 'required|string']);

        $review = Review::findOrFail($reviewId);

        $review->replies()->create([
            'user_id' => auth()->id(),
            'reply_text' => $request->reply_text
        ]);

        return back()->with('success', 'Admin đã phản hồi đánh giá!');
    }

    public function toggleReviewVisibility($reviewId)
    {
        $review = Review::findOrFail($reviewId);
        $review->update(['is_hidden' => !$review->is_hidden]);

        return back()->with('success', $review->is_hidden ? 'Đánh giá đã bị ẩn' : 'Đánh giá đã hiển thị lại');
    }
}
