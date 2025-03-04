<?php

namespace App\Http\Controllers;

use App\Models\CategoryPost;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryPostRequest;

class CategoryPostController extends Controller
{
    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-category-posts');
        $this->middleware('permission:create-category-posts', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-category-posts', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-category-posts', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'title' => $request->input('title'),
        ];

        $listCategoryPost = CategoryPost::all();
        return view('admins.categoryposts.index', compact('listCategoryPost'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryPostRequest $request)
    {
        $validated = $request->validated();

        CategoryPost::create([
            'title' => $validated['title'],
        ]);
        return redirect()->route('category-posts.index')->with('success', 'Tạo mới chuyên mục thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = CategoryPost::findOrFail($id);
        $category->title = $request->title;
        $category->save();

        return redirect()->route('category-posts.index')->with('success', 'Chuyên mục đã được cập nhật!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = CategoryPost::find($id);

        if (!$category) {
            return redirect()->route('category-posts.index')->with('error', 'Không tìm thấy chuyên mục!');
        }

        $category->delete();
        return redirect()->route('category-posts.index')->with('success', 'Chuyên mục đã được xóa thành công!');
    }
}
