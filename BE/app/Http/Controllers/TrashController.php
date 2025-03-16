<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Variant;
use App\Models\VariantValue;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrashController extends Controller
{
    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-dashboard');
        $this->middleware('permission:restore-products', ['only' => ['update']]);
        $this->middleware('permission:delete-products', ['only' => ['destroy']]);
    }

    public function index()
    {
        $type = request()->segment(3);
        switch($type) {
            case 'trash-products':
                $items = Product::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.products', compact('items'));
            case 'trash-categories':
                $items = Category::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.categories', compact('items'));
            case 'trash-variants':
                $items = Variant::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.variants', compact('items'));
            case 'trash-variant-values':
                $items = VariantValue::onlyTrashed()->with('variant')->latest()->paginate(10);
                return view('admins.trash.variant-values', compact('items'));
            default:
                abort(404);
        }
    }

    public function update($id)
    {
        try {
            $type = request()->segment(3);
            switch($type) {
                case 'trash-products':
                    $item = Product::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-categories':
                    $item = Category::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-variants':
                    $item = Variant::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-variant-values':
                    $item = VariantValue::onlyTrashed()->findOrFail($id);
                    break;
                default:
                    abort(404);
            }

            $item->restore();
            
            // Xử lý URL redirect
            $redirectUrl = '/admin/trash/';
            switch($type) {
                case 'trash-products':
                    $redirectUrl .= 'trash-products';
                    break;
                case 'trash-categories':
                    $redirectUrl .= 'trash-categories';
                    break;
                case 'trash-variants':
                    $redirectUrl .= 'trash-variants';
                    break;
                case 'trash-variant-values':
                    $redirectUrl .= 'trash-variant-values';
                    break;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Khôi phục thành công',
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $type = request()->segment(3);
            $force = filter_var(request()->input('force', false), FILTER_VALIDATE_BOOLEAN);
            $hasProducts = false;
            $message = '';

            switch($type) {
                case 'trash-products':
                    $item = Product::onlyTrashed()->findOrFail($id);
                    if ($item->image_thumbnail) {
                        Storage::disk('public')->delete($item->image_thumbnail);
                    }
                    foreach ($item->gallery as $image) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                    break;
                case 'trash-categories':
                    $item = Category::onlyTrashed()->findOrFail($id);
                    $productsCount = Product::withTrashed()->where('category_id', $id)->count();
                    if ($productsCount > 0 && !$force) {
                        return response()->json([
                            'success' => false,
                            'hasProducts' => true,
                            'message' => "Đang có {$productsCount} sản phẩm sử dụng danh mục này. Bạn không thể xóa?"
                        ], 200);
                    }
                    if ($force) {
                        Product::withTrashed()->where('category_id', $id)->update(['category_id' => null]);
                    }
                    break;
                case 'trash-variants':
                    $item = Variant::onlyTrashed()->findOrFail($id);
                    $productIds = ProductVariant::where('variant_id', $id)
                                    ->pluck('product_id')
                                    ->unique();
                    $productsCount = $productIds->count();
                    if ($productsCount > 0 && !$force) {
                        return response()->json([
                            'success' => false,
                            'hasProducts' => true,
                            'message' => "Đang có {$productsCount} sản phẩm sử dụng biến thể này. Bạn có chắc chắn muốn xóa?"
                        ], 200);
                    }
                    if ($force) {
                        ProductVariant::withTrashed()->where('variant_id', $id)->forceDelete();
                    }
                    break;
                case 'trash-variant-values':
                    $item = VariantValue::onlyTrashed()->findOrFail($id);
                    $productIds = ProductVariant::where('variant_value_id', $id)
                                    ->pluck('product_id')
                                    ->unique();
                    $productsCount = $productIds->count();
                    if ($productsCount > 0 && !$force) {
                        return response()->json([
                            'success' => false,
                            'hasProducts' => true,
                            'message' => "Đang có {$productsCount} sản phẩm sử dụng giá trị biến thể này. Bạn có chắc chắn muốn xóa?"
                        ], 200);
                    }
                    if ($force) {
                        ProductVariant::withTrashed()->where('variant_value_id', $id)->forceDelete();
                    }
                    break;
                default:
                    abort(404);
            }

            $item->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn thành công'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in force delete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn: ' . $e->getMessage()
            ], 500);
        }
    }
} 