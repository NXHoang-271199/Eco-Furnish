<?php

namespace App\Http\Controllers;

use App\Models\Post;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\CategoryPost;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $filters = [
            'title' => $request->input('title'),
        ];

        $years = Post::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->get();

        $postsByYear = [];
        foreach ($years as $year) {
            $postsByYear[$year->year] = Post::whereYear('created_at', $year->year)->count();
        }

        $featuredPosts = Post::orderBy('created_at', 'desc')->limit(3)->get();

        $listPosts = Post::search($filters)->orderByDesc('id')->paginate(5);

        $listCategoryPost = CategoryPost::all();
        foreach ($listPosts as $post) {
            $post->short_content = $this->limitHtml($post->content, 150);
        }
        return view('admins.posts.index', compact('listPosts', 'listCategoryPost', 'years', 'postsByYear', 'featuredPosts'));
    }

    public function limitHtml($html, $limit = 150, $end = '...')
    {
        $text = strip_tags(html_entity_decode($html));

        $limitedText = Str::limit($text, $limit, $end);

        return $limitedText;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $listCategoryPost = CategoryPost::all();
        $users = User::all();
        return view('admins.posts.create', compact('listCategoryPost', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        $validated = $request->validated();
        $content = $request->input('content');

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $images = $dom->getElementsByTagName('img');
        $uploadPath = 'uploads/posts/' . date('Y/m/d');
        if (!File::exists(public_path($uploadPath))) {
            File::makeDirectory(public_path($uploadPath), 0777, true);
        }
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            if (str_starts_with($src, '/uploads/')) {
                continue;
            }

            try {
                $imageContent = file_get_contents($src);
                if ($imageContent === false) {
                    continue;
                }
                $filename = uniqid() . '_' . time() . '.jpg';
                $newPath = $uploadPath . '/' . $filename;
                File::put(public_path($newPath), $imageContent);
                $image->setAttribute('src', '/' . $newPath);
            } catch (\Exception $e) {
                \Log::error('Lỗi khi tải ảnh: ' . $src . ' Chi tiết lỗi: ' . $e->getMessage());
                continue;
            }
        }

        $updatedContent = $dom->saveHTML();

        $filePath = null;
        if ($request->hasFile('image_thumbnail')) {
            $filePath = $request->file('image_thumbnail')->store('upload/blogs', 'public');
        }
        $slug = Str::slug($request->title);

        Post::create([
            'title' => $validated['title'],
            'content' => $updatedContent,
            'image_thumbnail' => $filePath,
            'category_post_id' => $validated['category_id'],
            'user_id' => $validated['user_id'],
            'status' => '1',
            'slug' => $slug,
        ]);
        return redirect()->route('posts.index')
            ->with('success', 'Bài viết đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singerPost = Post::findOrFail($id);
        return view('admins.posts.show', compact('singerPost'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $singerPost = Post::findOrFail($id);
        $listCategoryPost = CategoryPost::all();
        $users = User::all();
        return view('admins.posts.edit', compact('singerPost', 'listCategoryPost', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, string $id)
    {
        $singerPost = Post::findOrFail($id);

        $validated = $request->validated();

        $content = $request->input('content');
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $images = $dom->getElementsByTagName('img');
        $uploadPath = 'uploads/posts/' . date('Y/m/d');
        if (!File::exists(public_path($uploadPath))) {
            File::makeDirectory(public_path($uploadPath), 0777, true);
        }

        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            if (str_starts_with($src, '/uploads/')) {
                continue;
            }

            try {
                $imageContent = file_get_contents($src);
                if ($imageContent === false) {
                    continue;
                }
                $filename = uniqid() . '_' . time() . '.jpg';
                $newPath = $uploadPath . '/' . $filename;
                File::put(public_path($newPath), $imageContent);
                $image->setAttribute('src', '/' . $newPath);
            } catch (\Exception $e) {
                \Log::error('Lỗi khi tải ảnh: ' . $src . ' Chi tiết lỗi: ' . $e->getMessage());
                continue;
            }
        }

        $updatedContent = $dom->saveHTML();

        if ($request->hasFile('image_thumbnail')) {
            if ($singerPost->image_thumbnail && File::exists(public_path($singerPost->image_thumbnail))) {
                File::delete(public_path($singerPost->image_thumbnail));
            }

            $filePath = $request->file('image_thumbnail')->store('upload/blogs', 'public');
            $singerPost->image_thumbnail = $filePath;
        }
        $slug = Str::slug($request->title);

        $singerPost->update([
            'title' => $validated['title'],
            'content' => $updatedContent,
            'category_post_id' => $validated['category_id'],
            'user_id' => $validated['user_id'],
            'status' => '1',
            'slug' => $slug,
        ]);
        return redirect()->route('posts.index')->with('success', 'Cập nhật bài viết thành công!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Tìm bài viết cần xóa
        $post = Post::findOrFail($id);

        // Kiểm tra và xóa thumbnail nếu có
        if ($post->image_thumbnail && File::exists(public_path($post->image_thumbnail))) {
            File::delete(public_path($post->image_thumbnail));
        }

        // Xóa bài viết
        $post->delete();

        // Quay lại trang danh sách với thông báo thành công
        return redirect()->route('posts.index')->with('success', 'Bài viết đã được xóa thành công!');
    }
}
