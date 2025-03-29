<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // Thêm thông tin giá và số lượng vào response
            $products->getCollection()->transform(function ($product) {
                // Nếu sản phẩm có biến thể
                if ($product->has_variants) {
                    // Tính giá thấp nhất và cao nhất từ các biến thể
                    $minPrice = $product->variants->min('price');
                    $maxPrice = $product->variants->max('price');
                    $minDiscountPrice = $product->variants->min('discount_price');
                    $maxDiscountPrice = $product->variants->max('discount_price');
                    
                    // Tổng số lượng từ các biến thể
                    $totalQuantity = $product->variants->sum('quantity');
                    
                    $product->price_range = [
                        'min' => $minPrice,
                        'max' => $maxPrice != $minPrice ? $maxPrice : null,
                        'min_discount' => $minDiscountPrice,
                        'max_discount' => $maxDiscountPrice != $minDiscountPrice ? $maxDiscountPrice : null
                    ];
                    
                    $product->total_quantity = $totalQuantity;
                    $product->makeVisible(['total_quantity', 'price_range']);
                } else {
                    // Nếu không có biến thể, sử dụng giá và số lượng của sản phẩm
                    $product->makeVisible(['quantity']);
                }
                
                return $product;
            });
            
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
            // Eager loading tất cả các mối quan hệ cần thiết
            $product = Product::with(['category', 'gallery', 'variants'])
                ->findOrFail($id);

            // Xử lý thông tin giá và số lượng
            if ($product->has_variants) {
                // Tính giá thấp nhất và cao nhất từ các biến thể
                $minPrice = $product->variants->min('price');
                $maxPrice = $product->variants->max('price');
                $minDiscountPrice = $product->variants->min('discount_price');
                $maxDiscountPrice = $product->variants->max('discount_price');
                
                // Tổng số lượng từ các biến thể
                $totalQuantity = $product->variants->sum('quantity');
                
                $product->price_range = [
                    'min' => $minPrice,
                    'max' => $maxPrice != $minPrice ? $maxPrice : null,
                    'min_discount' => $minDiscountPrice,
                    'max_discount' => $maxDiscountPrice != $minDiscountPrice ? $maxDiscountPrice : null
                ];
                
                $product->total_quantity = $totalQuantity;
                $product->makeVisible(['total_quantity', 'price_range']);
                
                // Lấy tất cả dữ liệu variant và variant_value một lần
                $variantData = DB::table('variants')->get()->keyBy('id');
                $variantValueData = DB::table('variant_values')->get()->keyBy('id');
                
                // Xử lý dữ liệu biến thể để thêm thông tin chi tiết
                $product->variants->transform(function ($variant) use ($variantData, $variantValueData) {
                    $variantDetailsDisplay = [];
                    
                    if (!empty($variant->variant_details)) {
                        foreach ($variant->variant_details as $variantId => $valueId) {
                            // Lấy thông tin về variant và variant_value từ dữ liệu đã cached
                            $variantInfo = $variantData->get($variantId);
                            $variantValueInfo = $variantValueData->get($valueId);
                            
                            if ($variantInfo && $variantValueInfo) {
                                $variantDetailsDisplay[] = [
                                    'variant_id' => $variantId,
                                    'variant_name' => $variantInfo->name,
                                    'value_id' => $valueId,
                                    'value' => $variantValueInfo->value,
                                    'full_description' => $variantInfo->name . ': ' . $variantValueInfo->value
                                ];
                            }
                        }
                    }
                    
                    $variant->variant_details_display = $variantDetailsDisplay;
                    return $variant;
                });
            } else {
                // Nếu không có biến thể, hiển thị giá và số lượng của sản phẩm
                $product->makeVisible(['quantity']);
            }

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
            $query = Product::query()->with(['category', 'gallery', 'variants']);

            if ($request->has('keyword')) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Xử lý tìm kiếm theo giá
            if ($request->has('price_min') || $request->has('price_max')) {
                $query->where(function($q) use ($request) {
                    // Kiểm tra sản phẩm không có biến thể
                    $q->where(function($subQ) use ($request) {
                        $subQ->where('has_variants', false);
                        
                        if ($request->has('price_min')) {
                            $subQ->where(function($priceQ) use ($request) {
                                $priceQ->where('price', '>=', $request->price_min)
                                      ->orWhere(function($discountQ) use ($request) {
                                          $discountQ->where('discount_price', '>=', $request->price_min)
                                                  ->whereNotNull('discount_price');
                                      });
                            });
                        }
                        
                        if ($request->has('price_max')) {
                            $subQ->where(function($priceQ) use ($request) {
                                $priceQ->where('price', '<=', $request->price_max)
                                      ->orWhere(function($discountQ) use ($request) {
                                          $discountQ->where('discount_price', '<=', $request->price_max)
                                                  ->whereNotNull('discount_price');
                                      });
                            });
                        }
                    });
                    
                    // Kiểm tra sản phẩm có biến thể
                    $q->orWhere(function($subQ) use ($request) {
                        $subQ->where('has_variants', true)
                             ->whereHas('variants', function($variantQ) use ($request) {
                                 if ($request->has('price_min')) {
                                     $variantQ->where(function($priceQ) use ($request) {
                                         $priceQ->where('price', '>=', $request->price_min)
                                               ->orWhere(function($discountQ) use ($request) {
                                                   $discountQ->where('discount_price', '>=', $request->price_min)
                                                           ->whereNotNull('discount_price');
                                               });
                                     });
                                 }
                                 
                                 if ($request->has('price_max')) {
                                     $variantQ->where(function($priceQ) use ($request) {
                                         $priceQ->where('price', '<=', $request->price_max)
                                               ->orWhere(function($discountQ) use ($request) {
                                                   $discountQ->where('discount_price', '<=', $request->price_max)
                                                           ->whereNotNull('discount_price');
                                               });
                                     });
                                 }
                             });
                    });
                });
            }

            // Xử lý tìm kiếm theo số lượng
            if ($request->has('quantity_min') || $request->has('quantity_max')) {
                $query->where(function($q) use ($request) {
                    // Kiểm tra sản phẩm không có biến thể
                    $q->where(function($subQ) use ($request) {
                        $subQ->where('has_variants', false);
                        
                        if ($request->has('quantity_min')) {
                            $subQ->where('quantity', '>=', $request->quantity_min);
                        }
                        
                        if ($request->has('quantity_max')) {
                            $subQ->where('quantity', '<=', $request->quantity_max);
                        }
                    });
                    
                    // Kiểm tra sản phẩm có biến thể
                    $q->orWhere(function($subQ) use ($request) {
                        $subQ->where('has_variants', true)
                             ->whereHas('variants', function($variantQ) use ($request) {
                                 if ($request->has('quantity_min')) {
                                     $variantQ->where('quantity', '>=', $request->quantity_min);
                                 }
                                 
                                 if ($request->has('quantity_max')) {
                                     $variantQ->where('quantity', '<=', $request->quantity_max);
                                 }
                             });
                    });
                });
            }

            $products = $query->orderBy('created_at', 'desc')
                ->paginate(12);

            // Thêm thông tin giá và số lượng vào response
            $products->getCollection()->transform(function ($product) {
                if ($product->has_variants) {
                    // Tính giá thấp nhất và cao nhất từ các biến thể
                    $minPrice = $product->variants->min('price');
                    $maxPrice = $product->variants->max('price');
                    $minDiscountPrice = $product->variants->min('discount_price');
                    $maxDiscountPrice = $product->variants->max('discount_price');
                    
                    // Tổng số lượng từ các biến thể
                    $totalQuantity = $product->variants->sum('quantity');
                    
                    $product->price_range = [
                        'min' => $minPrice,
                        'max' => $maxPrice != $minPrice ? $maxPrice : null,
                        'min_discount' => $minDiscountPrice,
                        'max_discount' => $maxDiscountPrice != $minDiscountPrice ? $maxDiscountPrice : null
                    ];
                    
                    $product->total_quantity = $totalQuantity;
                    $product->makeVisible(['total_quantity', 'price_range']);
                } else {
                    // Nếu không có biến thể, sử dụng giá và số lượng của sản phẩm
                    $product->makeVisible(['quantity']);
                }
                
                return $product;
            });

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