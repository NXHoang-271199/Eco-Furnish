<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-categories');
        $this->middleware('permission:create-categories', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-categories', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-categories', ['only' => ['destroy']]);
        $this->middleware('permission:restore-categories', ['only' => ['restore']]);
    }

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
            
            // Kiểm tra danh mục đã tồn tại trong thùng rác
            $existingCategory = Category::withTrashed()
                ->where('name', $request->name)
                ->first();

            if ($existingCategory && $existingCategory->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Danh mục này đã tồn tại và hiện đang ở trong thùng rác, xin vui lòng khôi phục lại',
                    'category_in_trash' => true,
                    'category_id' => $existingCategory->id
                ], 422);
            }
            
            $data = $request->validated();
            // Tự động tạo slug từ tên
            $data['slug'] = Str::slug($data['name']);
            
            $category = Category::create($data);
            
            DB::commit();

            session()->flash('success', 'Thêm danh mục thành công');

            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name
                ],
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
                'message' => 'Cập nhật danh mục thành công'
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

            session()->flash('success', 'Danh mục đã được xóa thành công');

            return response()->json([
                'success' => true,
                'redirect' => route('categories.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa danh mục.'
            ], 500);
        }
    }

    /**
     * Restore the specified category from trash.
     */
    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $category = Category::withTrashed()->findOrFail($id);
            $category->restore();

            DB::commit();

            return redirect()->route('categories.index')
                ->with('success', 'Danh mục đã được khôi phục thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring category: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi khôi phục danh mục');
        }
    }
} 