<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryPost;
use App\Models\Post;
use Illuminate\Support\Str;

class CategoryPostApiController extends Controller
{
    public function index()
    {
        $categories = CategoryPost::withCount('posts')
            ->has('posts') // Chỉ lấy categories có bài viết
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => Str::slug($category->title), // Tạo slug từ title
                    'posts_count' => $category->posts_count
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function show($slug)
    {
        $category = CategoryPost::with(['posts' => function($query) {
            $query->where('status', 1) // Chỉ lấy bài viết đã xuất bản
                  ->orderBy('created_at', 'desc')
                  ->with('user'); // Kèm thông tin người đăng
        }])
        ->where('title', 'LIKE', Str::title(str_replace('-', ' ', $slug))) // Chuyển slug thành title
        ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => Str::slug($category->title),
                'posts' => $category->posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'thumbnail' => $post->image_thumbnail,
                        'author' => [
                            'name' => $post->user->name,
                            'slug' => Str::slug($post->user->name)
                        ],
                        'created_at' => $post->created_at->format('d/m/Y')
                    ];
                })
            ]
        ]);
    }
} 