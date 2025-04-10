<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\CategoryPost;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class PostApiController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::where('status', '1')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) {
                    $thumbnailUrl = null;
                    if (!empty($post->image_thumbnail) && is_string($post->image_thumbnail)) {
                        try {
                            $thumbnailUrl = asset('storage/' . $post->image_thumbnail);
                        } catch (\Exception $e) {
                            Log::warning("Error generating asset URL for thumbnail: {$post->image_thumbnail} in Post ID: {$post->id} - " . $e->getMessage());
                        }
                    }

                    $shortContent = '';
                    if (!empty($post->content)) {
                        try {
                            $cleanedContent = mb_convert_encoding($post->content, 'UTF-8', 'UTF-8');
                            $strippedContent = strip_tags($cleanedContent);
                            $shortContent = mb_substr($strippedContent, 0, 200, 'UTF-8') . '...';
                        } catch (\Exception $e) {
                            Log::warning("Error processing content for Post ID: {$post->id} - " . $e->getMessage());
                        }
                    }

                    $createdAtFormatted = null;
                    if ($post->created_at instanceof Carbon) {
                        try {
                            $createdAtFormatted = $post->created_at->format('d/m/Y');
                        } catch (\Exception $e) {
                            Log::warning("Error formatting created_at for Post ID: {$post->id} - " . $e->getMessage());
                        }
                    } elseif ($post->created_at) {
                        Log::warning("created_at is not a Carbon instance for Post ID: {$post->id}");
                    }

                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'thumbnail' => $thumbnailUrl,
                        'short_content' => $shortContent,
                        'category' => [
                            'id' => null,
                            'title' => 'Không có chuyên mục'
                        ],
                        'author' => [
                            'name' => 'Anonymous'
                        ],
                        'created_at' => $createdAtFormatted
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            // Log lỗi để kiểm tra sau
            Log::error('Error fetching all posts: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
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
                    'author' => $relatedPost->user?->name ?? 'Anonymous',
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
                    'id' => $post->categoryPost?->id,
                    'title' => $post->categoryPost?->title ?? 'Không có chuyên mục'
                ],
                'author' => [
                    'name' => $post->user?->name ?? 'Anonymous',
                    'avatar' => $post->user?->avatar ? asset('storage/' . $post->user->avatar) : null
                ],
                'created_at' => $post->created_at->format('d/m/Y'),
                'related_posts' => $relatedPosts
            ]
        ]);
    }

    public function getByCategory($categorySlug)
    {
        try {
            $category = CategoryPost::all()->first(function ($category) use ($categorySlug) {
                return Str::slug($category->title) === $categorySlug;
            });

            if (!$category) {
                return response()->json(['status' => 'error', 'message' => 'Category not found'], 404);
            }

            $posts = Post::where('category_post_id', $category->id)
                ->where('status', '1')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) {
                    $thumbnailUrl = null;
                    if (!empty($post->image_thumbnail) && is_string($post->image_thumbnail)) {
                        try {
                            $thumbnailUrl = asset('storage/' . $post->image_thumbnail);
                        } catch (\Exception $e) {
                            Log::warning("Error generating asset URL for thumbnail: {$post->image_thumbnail} in Post ID: {$post->id} - " . $e->getMessage());
                        }
                    }

                    $shortContent = '';
                    if (!empty($post->content)) {
                        try {
                            $cleanedContent = mb_convert_encoding($post->content, 'UTF-8', 'UTF-8');
                            $strippedContent = strip_tags($cleanedContent);
                            $shortContent = mb_substr($strippedContent, 0, 200, 'UTF-8') . '...';
                        } catch (\Exception $e) {
                            Log::warning("Error processing content for Post ID: {$post->id} - " . $e->getMessage());
                        }
                    }

                    $createdAtFormatted = null;
                    if ($post->created_at instanceof Carbon) {
                        try {
                            $createdAtFormatted = $post->created_at->format('d/m/Y');
                        } catch (\Exception $e) {
                            Log::warning("Error formatting created_at for Post ID: {$post->id} - " . $e->getMessage());
                        }
                    } elseif ($post->created_at) {
                        Log::warning("created_at is not a Carbon instance for Post ID: {$post->id}");
                    }

                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'thumbnail' => $thumbnailUrl,
                        'short_content' => $shortContent,
                        'category' => [
                            'id' => null,
                            'title' => 'Không có chuyên mục'
                        ],
                        'author' => [
                            'name' => 'Anonymous'
                        ],
                        'created_at' => $createdAtFormatted
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            // Log lỗi để kiểm tra sau
            Log::error('Error fetching posts by category: ' . $categorySlug . ' - ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}