<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // 1. Đánh giá sản phẩm
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', auth()->id())
            ->whereHas('products', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Bạn chưa mua sản phẩm này hoặc đơn hàng không chứa sản phẩm này.'], 403);
        }

        if (Review::where('user_id', auth()->id())
            ->where('order_id', $request->order_id)
            ->where('product_id', $request->product_id)
            ->exists()) {
            return response()->json(['error' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi.'], 400);
        }

        $review = Review::create([
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('review_images', 'public');
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_url' => $path,
                ]);
            }
        }

        return response()->json(['success' => 'Đánh giá đã được gửi.', 'review' => $review], 201);
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['reply_text' => 'required|string']);

        $review = Review::findOrFail($id);
        if ($review->user_id != auth()->id()) {
            return response()->json(['error' => 'Bạn không thể phản hồi đánh giá này.'], 403);
        }

        $reply = ReviewReply::create([
            'review_id' => $id,
            'user_id' => auth()->id(),
            'reply_text' => $request->reply_text,
        ]);
        return response()->json(['success' => 'Bạn đã phản hồi đánh giá.', 'reply' => $reply], 201);
    }
}
