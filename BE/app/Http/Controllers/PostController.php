<?php

namespace App\Http\Controllers;

use App\Models\Post;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\CategoryPost;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-posts');
        $this->middleware('permission:create-posts', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-posts', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-posts', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $filters = [
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'category_post_id' => $request->input('category_post_id'),
            'year' => $request->input('year'),
        ];

        $years = Post::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->get();

        $postsByYear = [];
        foreach ($years as $year) {
            $postsByYear[$year->year] = Post::whereYear('created_at', $year->year)->count();
        }

        $featuredPosts = Post::where('status', '1')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $listPosts = Post::search($filters)->orderByDesc('created_at')->paginate(10);

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
        $listUsers = User::all();
        return view('admins.posts.create', compact('listCategoryPost', 'listUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        $validated = $request->validated();
        
        // Xử lý thumbnail
        $filePath = null;
        if ($request->hasFile('image_thumbnail')) {
            $filePath = $request->file('image_thumbnail')->store('uploads/blogs', 'public');
        }
        
        $slug = Str::slug($request->title);
        
        Post::create([
            'title' => $validated['title'],
            'content' => $request->input('content'), // Không cần xử lý content nữa vì ảnh đã được upload trước
            'image_thumbnail' => $filePath,
            'category_post_id' => $validated['category_id'],
            'user_id' => Auth::user()->id,
            'status' => $request->input('status'),
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
        $listUsers = User::all();
        return view('admins.posts.edit', compact('singerPost', 'listCategoryPost', 'listUsers'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(PostRequest $request, string $id)
    {
        $singerPost = Post::findOrFail($id);
        $validated = $request->validated();
        
        // Xử lý thumbnail
        if ($request->hasFile('image_thumbnail')) {
            if ($singerPost->image_thumbnail) {
                Storage::disk('public')->delete($singerPost->image_thumbnail);
            }
            $filePath = $request->file('image_thumbnail')->store('uploads/blogs', 'public');
            $singerPost->image_thumbnail = $filePath;
        }
        
        $slug = Str::slug($request->title);
        
        $singerPost->update([
            'title' => $validated['title'],
            'content' => $request->input('content'), // Không cần xử lý content nữa vì ảnh đã được upload trước
            'category_post_id' => $validated['category_id'],
            'user_id' => Auth::user()->id,
            'status' => $request->input('status'),
            'slug' => $slug,
        ]);
        
        return redirect()->route('posts.index')
            ->with('success', 'Cập nhật bài viết thành công!');
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
