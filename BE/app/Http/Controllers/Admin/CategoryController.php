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
            return response()->json([
                'success' => true,
                'message' => 'Thêm danh mục thành công',
                'redirect' => route('categories.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Category $category)
    {
        return view('admins.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            // Tự động cập nhật slug từ tên
            $data['slug'] = Str::slug($data['name']);
            
            $category->update($data);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công',
                'redirect' => route('categories.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
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