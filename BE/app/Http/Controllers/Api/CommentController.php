<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
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

        $comment = Comment::create([
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
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
