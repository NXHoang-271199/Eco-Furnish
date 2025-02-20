<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admins.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admins.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            // Tự động tạo slug từ tên
            $data['slug'] = Str::slug($data['name']);
            
            Category::create($data);
            
            DB::commit();
            return redirect()->route('categories.index')
                ->with('success', 'Thêm danh mục thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating category: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi thêm danh mục: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        return view('admins.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $category->update($request->validated());
            return redirect()->route('categories.index')->with('success', 'Cập nhật danh mục thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật danh mục');
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục này vì đang có sản phẩm thuộc danh mục.'
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Danh mục đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa danh mục.'
            ], 500);
        }
    }
} 