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
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

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
                $items = Product::onlyTrashed()
                    ->with(['variants' => function($query) {
                        $query->onlyTrashed()->orderBy('price');
                    }])
                    ->latest()
                    ->paginate(10);
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
                    // Khôi phục sản phẩm
                    $item->restore();
                    
                    // Khôi phục các ảnh gallery của sản phẩm mà vẫn còn tồn tại trong storage
                    $galleryImages = $item->gallery()->onlyTrashed()->get();
                    foreach ($galleryImages as $image) {
                        if (Storage::disk('public')->exists($image->image_url)) {
                            $image->restore();
                        } else {
                            // Nếu ảnh không tồn tại trong storage, xóa vĩnh viễn record
                            $image->forceDelete();
                        }
                    }
                    
                    // Khôi phục các biến thể của sản phẩm
                    $item->variants()->onlyTrashed()->restore();
                    
                    // Kiểm tra thêm nếu có relation khác (như ProductVariant)
                    if (method_exists($item, 'productVariant')) {
                        $item->productVariant()->onlyTrashed()->restore();
                    }
                    break;
                case 'trash-categories':
                    $item = Category::onlyTrashed()->findOrFail($id);
                    $item->restore();
                    break;
                case 'trash-variants':
                    $item = Variant::onlyTrashed()->findOrFail($id);
                    $item->restore();
                    break;
                case 'trash-variant-values':
                    $item = VariantValue::onlyTrashed()->findOrFail($id);
                    $item->restore();
                    break;
                default:
                    abort(404);
            }
            
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

                    // Kiểm tra xem sản phẩm có tồn tại trong bất kỳ OrderItem nào không
                    $isInOrder = OrderItem::where('product_id', $item->id)->exists();

                    if ($isInOrder) {
                        // Nếu sản phẩm có trong đơn hàng, trả về lỗi và không xóa
                        return response()->json([
                            'success' => false,
                            'message' => 'Sản phẩm này hiện đang có trong Order, không thể xóa sản phẩm'
                        ], 400); // Sử dụng HTTP status code 400 Bad Request
                    } else {
                        // Nếu sản phẩm không có trong đơn hàng, tiến hành xóa vĩnh viễn

                        // Xóa các product variants liên quan (nếu có và đã xóa mềm)
                        // Đảm bảo chúng ta chỉ xóa variant của sản phẩm này
                        ProductVariant::where('product_id', $item->id)->onlyTrashed()->forceDelete();

                        // Xóa ảnh thumbnail khỏi storage
                        if ($item->image_thumnail) {
                            Storage::disk('public')->delete($item->image_thumnail);
                        }

                        // Lấy các gallery images trước khi xóa các bản ghi
                        $galleryImages = $item->gallery()->onlyTrashed()->get();

                        // Xóa files ảnh gallery khỏi storage
                        foreach ($galleryImages as $image) {
                            Storage::disk('public')->delete($image->image_url);
                        }

                        // Xóa các bản ghi gallery
                        $item->gallery()->onlyTrashed()->forceDelete();

                        // Xóa vĩnh viễn bản ghi sản phẩm
                        $item->forceDelete();
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
                    // Thêm dòng code để xóa vĩnh viễn danh mục
                    $item->forceDelete();
                    break;
                case 'trash-variants':
                    $item = Variant::onlyTrashed()->findOrFail($id);
                    
                    // Chúng ta sẽ đếm chính xác số lượng sản phẩm chứa biến thể này
                    // bằng cách truy vấn trên bảng product_variants
                    
                    // Lấy tất cả sản phẩm có variant_details chứa biến thể này
                    $variantId = $id;
                    
                    // Tìm các sản phẩm có biến thể này trong variant_details
                    $products = \DB::table('products')
                        ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                        ->whereRaw("product_variants.variant_details LIKE ?", ["%\"$variantId\"%"])
                        ->where('products.deleted_at', null) // Chỉ đếm sản phẩm chưa bị xóa mềm
                        ->select('products.id', 'products.name')
                        ->distinct() // Đảm bảo mỗi sản phẩm chỉ được tính một lần
                        ->get();
                    
                    $productsCount = $products->count();
                    
                    // Nếu có sản phẩm đang sử dụng biến thể này, không cho phép xóa vĩnh viễn
                    if ($productsCount > 0) {
                        $productNames = $products->pluck('name')->take(3)->toArray();
                        $productNamesStr = implode(', ', $productNames);
                        
                        if (count($products) > 3) {
                            $productNamesStr .= ' và ' . (count($products) - 3) . ' sản phẩm khác';
                        }
                        
                        return response()->json([
                            'success' => false,
                            'hasProducts' => true,
                            'productsCount' => $productsCount,
                            'message' => "Không thể xóa vĩnh viễn vì có {$productsCount} sản phẩm đang sử dụng biến thể này: {$productNamesStr}"
                        ], 400);
                    }
                    
                    // Chỉ xóa vĩnh viễn khi không có sản phẩm nào sử dụng
                    $item->forceDelete();
                    break;
                
                case 'trash-variant-values':
                    $item = VariantValue::onlyTrashed()->findOrFail($id);
                    
                    // Tương tự như trên, kiểm tra sản phẩm sử dụng giá trị biến thể này
                    $valueId = $id;
                    
                    // Tìm các sản phẩm có giá trị biến thể này trong variant_details
                    $products = \DB::table('products')
                        ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                        ->whereRaw("product_variants.variant_details LIKE ?", ["%\"$valueId\"%"])
                        ->where('products.deleted_at', null) // Chỉ đếm sản phẩm chưa bị xóa mềm
                        ->select('products.id', 'products.name')
                        ->distinct() // Đảm bảo mỗi sản phẩm chỉ được tính một lần
                        ->get();
                    
                    $productsCount = $products->count();
                    
                    // Nếu có sản phẩm đang sử dụng giá trị biến thể này, không cho phép xóa vĩnh viễn
                    if ($productsCount > 0) {
                        $productNames = $products->pluck('name')->take(3)->toArray();
                        $productNamesStr = implode(', ', $productNames);
                        
                        if (count($products) > 3) {
                            $productNamesStr .= ' và ' . (count($products) - 3) . ' sản phẩm khác';
                        }
                        
                        return response()->json([
                            'success' => false,
                            'hasProducts' => true,
                            'productsCount' => $productsCount,
                            'message' => "Không thể xóa vĩnh viễn vì có {$productsCount} sản phẩm đang sử dụng giá trị biến thể này: {$productNamesStr}"
                        ], 400);
                    }
                    
                    // Chỉ xóa vĩnh viễn khi không có sản phẩm nào sử dụng
                    $item->forceDelete();
                    break;
                default:
                    abort(404);
            }

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