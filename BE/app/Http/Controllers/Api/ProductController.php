<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Lấy danh sách sản phẩm, có thể lọc theo category hoặc space
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query()->with(['category', 'gallery', 'variants' => function($query) {
                $query->whereNull('deleted_at');
            }]);

            // --- Lọc theo Category (giữ nguyên nếu cần) ---
            if ($request->has('category')) { // Giả sử FE gửi slug
                $categorySlug = $request->category;
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            // --- Lọc theo Space (MỚI) ---
            if ($request->has('space')) {
                $spaceKey = $request->space; // Lấy space_key từ request
                
                // Lấy danh sách category_id thuộc space này từ bảng trung gian
                $categoryIdsInSpace = DB::table('category_space')
                                          ->where('space_key', $spaceKey)
                                          ->pluck('category_id')
                                          ->unique(); // Lấy các ID duy nhất
                
                if ($categoryIdsInSpace->isNotEmpty()) {
                    // Lọc sản phẩm thuộc các category_id này
                    $query->whereIn('category_id', $categoryIdsInSpace);
                } else {
                     // Nếu không có category nào thuộc space này, trả về rỗng
                    $query->whereRaw('1 = 0'); // Điều kiện luôn sai
                }
            }

            // Lấy tất cả sản phẩm khớp với query, không phân trang
            $products = $query->orderBy('created_at', 'desc')
                              ->get(); 

            // Thêm thông tin giá và số lượng vào response
            $products->transform(function ($product) {
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
            
            // Trả về danh sách sản phẩm trực tiếp, không còn cấu trúc phân trang
            return response()->json([
                'status' => 'success',
                'data' => $products // Trả về collection sản phẩm
            ]);
        } catch (\Exception $e) {
            Log::error('API Get Products Error: ' . $e->getMessage());
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
            \Log::info("Đang lấy thông tin sản phẩm ID: " . $id);
            
            $product = Product::with([
                'category',
                'gallery' => function($query) {
                    $query->whereNull('deleted_at');
                },
                'variants' => function($query) {
                    $query->whereNull('deleted_at');
                }
            ])
                ->findOrFail($id);

            \Log::info("Đã tìm thấy sản phẩm:", ['product_id' => $product->id, 'has_variants' => $product->has_variants]);

            // Xử lý thông tin giá và số lượng
            if ($product->has_variants) {
                \Log::info("Sản phẩm có biến thể, đang xử lý thông tin biến thể");
                
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
                
                // Xử lý dữ liệu biến thể để thêm thông tin chi tiết
                $product->variants->transform(function ($variant) {
                    \Log::info("Xử lý biến thể:", ['variant_id' => $variant->id, 'variant_details' => $variant->variant_details]);
                    // variant_details đã được xử lý bởi accessor trong model
                    $variant->variant_details_display = $variant->variant_details;
                    return $variant;
                });
            } else {
                \Log::info("Sản phẩm không có biến thể");
                // Nếu không có biến thể, hiển thị giá và số lượng của sản phẩm
                $product->makeVisible(['quantity']);
            }

            \Log::info("Hoàn thành xử lý sản phẩm");
            return response()->json([
                'status' => 'success',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            \Log::error("Lỗi khi lấy thông tin sản phẩm: " . $e->getMessage(), [
                'product_id' => $id,
                'error' => $e->getTraceAsString()
            ]);
            
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
            $query = Product::query()->with(['category', 'gallery', 'variants' => function($query) {
                $query->whereNull('deleted_at');
            }]);

            if ($request->has('keyword')) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }

            if ($request->has('category')) {
                $categorySlug = $request->category;
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            } elseif ($request->has('space')) {
                $spaceKey = $request->space;
                $categoryIdsInSpace = DB::table('category_space')
                                          ->where('space_key', $spaceKey)
                                          ->pluck('category_id')
                                          ->unique();
                if ($categoryIdsInSpace->isNotEmpty()) {
                    $query->whereIn('category_id', $categoryIdsInSpace);
                } else {
                    $query->whereRaw('1 = 0');
                }
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

            // Lấy tất cả sản phẩm khớp, không phân trang
            $products = $query->orderBy('created_at', 'desc')
                ->get(); 

            // Thêm thông tin giá và số lượng vào response
            $products->transform(function ($product) {
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

            // Trả về danh sách sản phẩm trực tiếp
            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('API Search Products Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi tìm kiếm sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách sản phẩm bán chạy
     */
    public function getBestSellers()
    {
        try {
            // Lấy các sản phẩm bán chạy dựa trên số lượng đã bán
            $products = Product::with(['category', 'gallery', 'variants' => function($query) {
                $query->whereNull('deleted_at');
            }])
                ->select('products.*', DB::raw('(SELECT SUM(quantity) FROM order_items WHERE product_id = products.id) as sold_quantity'))
                ->whereNotNull(DB::raw('(SELECT SUM(quantity) FROM order_items WHERE product_id = products.id)'))
                ->orderBy('sold_quantity', 'desc')
                ->take(4)
                ->get();

            // Thêm thông tin giá và số lượng vào response
            $products->transform(function ($product) {
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
                'message' => 'Có lỗi xảy ra khi lấy danh sách sản phẩm bán chạy',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}