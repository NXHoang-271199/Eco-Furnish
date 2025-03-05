<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostApiController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'categoryPost'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function show($id)
    {
        $post = Post::with(['user', 'categoryPost'])->find($id);
        
        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }
} 