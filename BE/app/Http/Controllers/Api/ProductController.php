<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Lấy danh sách sản phẩm
     */
    public function index()
    {
        try {
            $products = Product::with(['category', 'gallery', 'variants'])
                ->orderBy('created_at', 'desc')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy danh sách sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một sản phẩm
     */
    public function show($id)
    {
        try {
            $product = Product::with(['category', 'gallery', 'variants.variant', 'variants.variantValue'])
                ->findOrFail($id);

            // Xử lý dữ liệu biến thể để thêm thông tin chi tiết
            $product->variants->each(function ($variant) {
                // Thêm thông tin tên biến thể và giá trị biến thể
                $variant->variant_name = $variant->variant ? $variant->variant->name : 'Không xác định';
                $variant->variant_value_name = $variant->variantValue ? $variant->variantValue->value : 'Không xác định';
                
                // Tạo mô tả đầy đủ cho biến thể
                $variant->full_description = $variant->variant_name . ': ' . $variant->variant_value_name;
            });

            return response()->json([
                'status' => 'success',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy thông tin sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tìm kiếm sản phẩm
     */
    public function search(Request $request)
    {
        try {
            $query = Product::query()->with(['category', 'gallery']);

            if ($request->has('keyword')) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('price_min')) {
                $query->where('price', '>=', $request->price_min);
            }

            if ($request->has('price_max')) {
                $query->where('price', '<=', $request->price_max);
            }

            $products = $query->orderBy('created_at', 'desc')
                ->paginate(12);

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi tìm kiếm sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}