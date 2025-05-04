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
    // Định nghĩa các không gian và tên hiển thị dùng chung
    protected $spaceTypes = [
        'living_room' => 'Phòng Khách',
        'bedroom' => 'Phòng Ngủ',
        'kitchen' => 'Phòng Bếp',
        'dining_room' => 'Phòng Ăn',
        'office' => 'Văn Phòng',
        'outdoor' => 'Ngoài Trời',
        'bathroom' => 'Phòng Tắm',
        'other' => 'Khác',
    ];

    // Thêm phương thức public để request có thể lấy $spaceTypes
    public function getSpaceTypes()
    {
        return $this->spaceTypes;
    }

    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-categories');
        $this->middleware('permission:create-categories', ['only' => ['store']]);
        $this->middleware('permission:update-categories', ['only' => ['getCategoryData', 'update']]);
        $this->middleware('permission:delete-categories', ['only' => ['destroy']]);
        $this->middleware('permission:restore-categories', ['only' => ['restore']]);
    }

    public function index()
    {
        // Eager load space keys để tối ưu
        $categories = Category::latest()->paginate(10);
        // Lấy space keys cho từng category thủ công nếu cần
        // $categories->each(function ($category) {
        //     $category->space_keys_list = $category->spaceKeys;
        // });

        return view('admins.categories.index', [
            'categories' => $categories,
            'spaceTypes' => $this->spaceTypes
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            // Kiểm tra trùng tên trước khi tạo mới
            $existingCategoryCheck = Category::where('name', $request->name)->whereNull('deleted_at')->first();
            if ($existingCategoryCheck) {
                 return back()->withErrors(['name' => 'Tên danh mục đã tồn tại.'], 'store') // Sử dụng error bag 'store'
                             ->withInput();
            }

            $data = $request->safe()->only('name'); // Chỉ lấy name từ validated data
            $data['slug'] = Str::slug($data['name']);

            $category = Category::create($data);

            // Lấy mảng các space keys từ request
            $spaceKeys = $request->input('spaces', []); // Mặc định là mảng rỗng

            // Đồng bộ hóa các không gian trong bảng trung gian
            $category->syncSpaces($spaceKeys);

            DB::commit();

            // Kiểm tra nếu là request AJAX
            if ($request->ajax() || $request->wantsJson()) {
                // Chuẩn bị dữ liệu không gian để trả về
                $spaceNames = [];
                foreach ($spaceKeys as $key) {
                    if (isset($this->spaceTypes[$key])) {
                        $spaceNames[$key] = $this->spaceTypes[$key];
                    }
                }
                
                // Trả về JSON response với thông tin category đầy đủ
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm danh mục thành công',
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'spaces' => $spaceKeys, // Trả về mảng spaces đã chọn
                        'spaceNames' => $spaceNames, // Thêm tên không gian
                        'spacesDisplay' => count($spaceKeys) > 0 ? implode(', ', array_map(function($key) {
                            return $this->spaceTypes[$key] ?? '';
                        }, $spaceKeys)) : 'Chưa phân loại' // Chuỗi đã định dạng sẵn
                    ]
                ]);
            }

            // Nếu không phải AJAX thì redirect như bình thường
            return redirect()->route('categories.index')
                         ->with('success', 'Thêm danh mục thành công');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating category: ' . $e->getMessage());
            
            // Kiểm tra nếu là AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm danh mục: ' . $e->getMessage(),
                    'errors' => ['general' => 'Có lỗi xảy ra, vui lòng thử lại.']
                ], 422);
            }
            
            // Redirect về index với thông báo lỗi và error bag
            return back()->with('error', 'Có lỗi xảy ra khi thêm danh mục: ' . $e->getMessage())
                         ->withErrors(['general' => 'Có lỗi xảy ra, vui lòng thử lại.'], 'store') // Thêm lỗi vào error bag 'store'
                         ->withInput();
        }
    }

    /**
     * Lấy dữ liệu chi tiết của category dưới dạng JSON cho JS
     */
    public function getCategoryData(Category $category)
    {
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục'], 404);
        }
        // Trả về category và mảng các space keys của nó
        return response()->json(['success' => true, 'data' => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'spaces' => $category->spaceKeys // Sử dụng accessor đã tạo
        ]]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            DB::beginTransaction();

            // Kiểm tra trùng tên (trừ chính nó)
             $existingCategoryCheck = Category::where('name', $request->name)
                                           ->where('id', '!=', $category->id)
                                           ->whereNull('deleted_at')->first();
             if ($existingCategoryCheck) {
                 // Redirect về index với lỗi và input cũ, sử dụng error bag 'update'
                 return redirect()->route('categories.index')
                              ->withErrors(['name' => 'Tên danh mục đã tồn tại.'], 'update')
                              ->withInput($request->except(['_token', '_method'])) // Giữ lại input trừ token và method
                              ->with('edit_id', $category->id); // Thêm id để JS biết cần mở lại form sửa nào
             }

            $data = $request->safe()->only('name'); // Chỉ lấy name
            $data['slug'] = Str::slug($data['name']);

            $category->update($data);

            // Lấy mảng các space keys từ request
            $spaceKeys = $request->input('spaces', []);

            // Đồng bộ hóa các không gian trong bảng trung gian
            $category->syncSpaces($spaceKeys);

            DB::commit();

            return redirect()->route('categories.index')
                         ->with('success', 'Cập nhật danh mục thành công'); // Redirect về index với thông báo

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating category: ' . $e->getMessage());
             // Redirect về index với thông báo lỗi, error bag và input cũ
             return redirect()->route('categories.index')
                          ->with('error', 'Có lỗi xảy ra khi cập nhật danh mục: ' . $e->getMessage())
                          ->withErrors(['general' => 'Có lỗi xảy ra, vui lòng thử lại.'], 'update')
                          ->withInput($request->except(['_token', '_method']))
                          ->with('edit_id', $category->id); // Thêm id để JS biết cần mở lại form sửa nào
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
