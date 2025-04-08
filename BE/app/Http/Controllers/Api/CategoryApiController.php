<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryApiController extends Controller
{
    /**
     * Hiển thị danh sách tất cả danh mục sản phẩm, nhóm theo không gian
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Lấy tất cả category cùng với space_keys của chúng
            $allCategories = Category::select('id', 'name', 'slug')->get();
            $categorySpaces = DB::table('category_space')->get()->groupBy('category_id');

            $categoriesWithSpaces = $allCategories->map(function ($category) use ($categorySpaces) {
                $spaces = $categorySpaces->get($category->id, collect())->pluck('space_key')->toArray();
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'spaces' => $spaces // Mảng các space_key
                ];
            });

            // Định nghĩa các không gian và tên hiển thị
            $spaceTypesMap = (new \App\Http\Controllers\CategoryController())->getSpaceTypes(); // Lấy từ CategoryController

            // Tạo cấu trúc nhóm theo không gian
            $groupedCategories = [];
            foreach ($spaceTypesMap as $key => $displayName) {
                $groupedCategories[$displayName] = [];
            }

            // Phân loại category vào các nhóm không gian
            foreach ($categoriesWithSpaces as $category) {
                if (empty($category['spaces'])) {
                    // Nếu không có không gian, cho vào nhóm 'Khác'
                    if (isset($groupedCategories['Khác'])) {
                         $groupedCategories['Khác'][] = ['id' => $category['id'], 'name' => $category['name'], 'slug' => $category['slug']];
                    }
                } else {
                    foreach ($category['spaces'] as $spaceKey) {
                        $displayName = $spaceTypesMap[$spaceKey] ?? 'Khác'; // Lấy tên hiển thị, mặc định là 'Khác'
                         if (isset($groupedCategories[$displayName])) {
                            // Chỉ thêm nếu chưa tồn tại trong nhóm này (tránh trùng lặp)
                            if (!collect($groupedCategories[$displayName])->contains('id', $category['id'])) {
                                 $groupedCategories[$displayName][] = ['id' => $category['id'], 'name' => $category['name'], 'slug' => $category['slug']];
                            }
                         }
                    }
                }
            }

            // Sắp xếp lại theo thứ tự mong muốn và loại bỏ nhóm trống
             $orderedGroupedCategories = collect($spaceTypesMap)
                ->mapWithKeys(function ($displayName, $key) use ($groupedCategories) {
                    return [$displayName => collect($groupedCategories[$displayName] ?? [])];
                })
                ->filter(function ($group) {
                     return $group->isNotEmpty();
                 });


            return response()->json([
                'success' => true,
                'data' => $orderedGroupedCategories
            ]);
        } catch (\Exception $e) {
            Log::error('API Get Categories Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách phẳng tất cả danh mục,
     * có thể lọc theo không gian nếu có tham số 'space'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        try {
            $query = Category::query()->select('id', 'name', 'slug');

            // Kiểm tra nếu có tham số lọc theo không gian
            if ($request->has('space')) {
                $spaceKey = $request->input('space');

                // Lấy danh sách category_id thuộc space này
                $categoryIdsInSpace = DB::table('category_space')
                                          ->where('space_key', $spaceKey)
                                          ->pluck('category_id')
                                          ->unique();
                
                // Chỉ lấy các category có ID trong danh sách trên
                $query->whereIn('id', $categoryIdsInSpace);
            }

            $categories = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('API Get All Categories Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy tất cả danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị thông tin chi tiết của một danh mục
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        try {
            $category = Category::where('slug', $slug)->first();
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }
            
            // Lấy thông tin các sản phẩm thuộc danh mục này nếu cần
            $products = $category->products()
                ->select('id', 'name', 'slug', 'price', 'discount_price', 'thumbnail')
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug
                    ],
                    'products' => $products
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin danh mục: ' . $e->getMessage()
            ], 500);
        }
    }
} 