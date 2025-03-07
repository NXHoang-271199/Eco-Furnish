<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the comments.
     */
    public function index(Request $request)
    {
        $comments = Comment::with(['user', 'product'])
            ->when($request->search, function ($query, $search) {
                return $query->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admins.comments.index', compact('comments'));
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
     * Toggle the status of the specified comment.
     */
    public function toggleStatus(Comment $comment)
    {
        $newStatus = $comment->status === 'Hiển thị' ? 'Ẩn' : 'Hiển thị';
        $comment->update(['status' => $newStatus]);

        return redirect()->route('comments.index')
            ->with('success', 'Trạng thái bình luận đã được cập nhật thành công.');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return redirect()->route('comments.index')
            ->with('success', 'Bình luận đã được xóa thành công.');
    }
} 