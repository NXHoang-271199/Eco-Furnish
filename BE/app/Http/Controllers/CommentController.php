<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Display a listing of the products with comments.
     */
    public function index(Request $request)
    {
        $products = Product::select('products.*', DB::raw('COUNT(comments.id) as comment_count'))
            ->join('comments', 'products.id', '=', 'comments.product_id')
            ->when($request->search, function ($query, $search) {
                return $query->where('products.name', 'like', "%{$search}%");
            })
            ->groupBy('products.id')
            ->orderBy('comment_count', 'desc')
            ->paginate(10);

        return view('admins.comments.index', compact('products'));
    }

    /**
     * Display comments for a specific product.
     */
    public function productComments(Product $product, Request $request)
    {
        $comments = Comment::with(['user', 'product'])
            ->where('product_id', $product->id)
            ->when($request->search, function ($query, $search) {
                return $query->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admins.comments.product_comments', compact('product', 'comments'));
    }

    /**
     * Display the specified comment.
     */
    public function show(Comment $comment)
    {
        $comment->load(['user', 'product']);
        return view('admins.comments.show', compact('comment'));
    }

    /**
     * Display user information.
     */
    public function userInfo(User $user)
    {
        $commentCount = Comment::where('user_id', $user->id)->count();
        return view('admins.comments.user_info', compact('user', 'commentCount'));
    }

    /**
     * Toggle the status of the specified comment.
     */
    public function toggleStatus(Comment $comment)
    {
        $newStatus = $comment->status === 'Hiển thị' ? 'Ẩn' : 'Hiển thị';
        $comment->update(['status' => $newStatus]);

        return back()->with('success', 'Trạng thái bình luận đã được cập nhật thành công.');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Bình luận đã được xóa thành công.');
    }
} 