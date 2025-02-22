<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrashController extends Controller
{
    public function products()
    {
        $products = Product::onlyTrashed()->latest()->paginate(10);
        return view('admins.trash.products', compact('products'));
    }

    public function categories()
    {
        $categories = Category::onlyTrashed()->latest()->paginate(10);
        return view('admins.trash.categories', compact('categories'));
    }

    public function variants()
    {
        $variants = Variant::onlyTrashed()->latest()->paginate(10);
        return view('admins.trash.variants', compact('variants'));
    }

    public function variantValues()
    {
        $variantValues = VariantValue::onlyTrashed()->latest()->paginate(10);
        return view('admins.trash.variant-values', compact('variantValues'));
    }

    public function restoreProduct($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            $product->restore();
            return response()->json([
                'success' => true,
                'message' => 'Khôi phục sản phẩm thành công',
                'redirect' => route('trash.products')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục sản phẩm'
            ], 500);
        }
    }

    public function restoreCategory($id)
    {
        try {
            $category = Category::onlyTrashed()->findOrFail($id);
            $category->restore();
            return response()->json([
                'success' => true,
                'message' => 'Khôi phục danh mục thành công',
                'redirect' => route('trash.categories')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục danh mục'
            ], 500);
        }
    }

    public function restoreVariant($id)
    {
        try {
            $variant = Variant::onlyTrashed()->findOrFail($id);
            $variant->restore();
            return response()->json([
                'success' => true,
                'message' => 'Khôi phục biến thể thành công',
                'redirect' => route('trash.variants')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục biến thể'
            ], 500);
        }
    }

    public function restoreVariantValue($id)
    {
        try {
            $variantValue = VariantValue::onlyTrashed()->findOrFail($id);
            $variantValue->restore();
            return response()->json([
                'success' => true,
                'message' => 'Khôi phục giá trị biến thể thành công',
                'redirect' => route('trash.variant-values')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục giá trị biến thể'
            ], 500);
        }
    }

    public function forceDeleteProduct($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            
            // Xóa ảnh đại diện
            if ($product->image_thumnail) {
                Storage::disk('public')->delete($product->image_thumnail);
            }
            
            // Xóa gallery images
            foreach ($product->gallery as $image) {
                Storage::disk('public')->delete($image->image_url);
            }
            
            $product->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn sản phẩm thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn sản phẩm'
            ], 500);
        }
    }

    public function forceDeleteCategory($id)
    {
        try {
            $category = Category::onlyTrashed()->findOrFail($id);
            
            // Kiểm tra xem danh mục có sản phẩm không
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa vĩnh viễn danh mục này vì đang có sản phẩm thuộc danh mục'
                ], 400);
            }
            
            $category->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn danh mục thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn danh mục'
            ], 500);
        }
    }

    public function forceDeleteVariant($id)
    {
        try {
            $variant = Variant::onlyTrashed()->findOrFail($id);
            
            // Kiểm tra xem biến thể có giá trị không
            if ($variant->values()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa vĩnh viễn biến thể này vì đang có giá trị biến thể'
                ], 400);
            }
            
            $variant->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn biến thể thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn biến thể'
            ], 500);
        }
    }

    public function forceDeleteVariantValue($id)
    {
        try {
            $variantValue = VariantValue::onlyTrashed()->findOrFail($id);
            
            // Kiểm tra xem giá trị biến thể có được sử dụng không
            if ($variantValue->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa vĩnh viễn giá trị biến thể này vì đang được sử dụng trong sản phẩm'
                ], 400);
            }
            
            $variantValue->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn giá trị biến thể thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn giá trị biến thể'
            ], 500);
        }
    }
} 