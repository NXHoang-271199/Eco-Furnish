<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostApiController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'categoryPost'])
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'thumbnail' => $post->image_thumbnail ? asset('storage/' . $post->image_thumbnail) : null,
                    'short_content' => substr(strip_tags($post->content), 0, 200) . '...',
                    'category' => [
                        'id' => $post->categoryPost->id ?? null,
                        'title' => $post->categoryPost->title ?? 'Không có chuyên mục'
                    ],
                    'author' => [
                        'name' => $post->user->name ?? 'Anonymous'
                    ],
                    'created_at' => $post->created_at ? $post->created_at->format('d/m/Y') : null
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function show($slug)
    {
        $post = Post::with(['user', 'categoryPost'])
            ->where('slug', $slug)
            ->where('status', '1')
            ->firstOrFail();

        $relatedPosts = Post::with(['user'])
            ->where('category_post_id', $post->category_post_id)
            ->where('id', '!=', $post->id)
            ->where('status', '1')
            ->limit(3)
            ->get()
            ->map(function ($relatedPost) {
                return [
                    'title' => $relatedPost->title,
                    'slug' => $relatedPost->slug,
                    'thumbnail' => $relatedPost->image_thumbnail ? asset('storage/' . $relatedPost->image_thumbnail) : null,
                    'author' => $relatedPost->user->name ?? 'Anonymous',
                    'created_at' => $relatedPost->created_at->format('d/m/Y')
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'thumbnail' => $post->image_thumbnail ? asset('storage/' . $post->image_thumbnail) : null,
                'category' => [
                    'id' => $post->categoryPost->id ?? null,
                    'title' => $post->categoryPost->title ?? 'Không có chuyên mục'
                ],
                'author' => [
                    'name' => $post->user->name ?? 'Anonymous',
                    'avatar' => $post->user->avatar ? asset('storage/' . $post->user->avatar) : null
                ],
                'created_at' => $post->created_at->format('d/m/Y'),
                'related_posts' => $relatedPosts
            ]
        ]);
    }
}