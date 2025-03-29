<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Lấy giỏ hàng của user
     */
    public function index()
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart || $cart->cartItems()->count() === 0) {
            return response()->json(['message' => 'Giỏ hàng trống'], 200);
        }

        $cartItems = $cart->cartItems()->with('product', 'productVariant')->get();

        // Tính tổng tiền từng sản phẩm
        $cartItems->transform(function ($item) {
            // Kiểm tra sự tồn tại của product
            if (!$item->product) {
                Log::error('Sản phẩm không tồn tại trong giỏ hàng ID: ' . $item->id);
                $item->total_price = 0;
                return $item;
            }

            if ($item->product_variant_id) {
                // Kiểm tra sự tồn tại của productVariant
                if (!$item->productVariant) {
                    Log::error('Biến thể không tồn tại cho sản phẩm ID: ' . $item->product_id);
                    $price = $item->product->discount_price ?? $item->product->price;
                } else {
                    $price = $item->productVariant->discount_price ?? $item->productVariant->price;
                }
            } else {
                $price = $item->product->discount_price ?? $item->product->price;
            }

            $item->total_price = $price * $item->quantity;
            return $item;
        });

        // Lọc các sản phẩm không tồn tại trước khi tính tổng
        $validCartItems = $cartItems->filter(function($item) {
            return $item->product !== null;
        });

        // Tính tổng tiền cả giỏ hàng từ các sản phẩm hợp lệ
        $totalCartPrice = $validCartItems->sum('total_price');

        return response()->json([
            'cart' => $cart,
            'items' => $validCartItems,
            'total_cart_price' => $totalCartPrice,
        ], 200);
    }


    /**
     * Thêm sản phẩm vào giỏ hàng (có kiểm tra số lượng tồn kho)
     */
    public function addToCart(Request $request)
    {
        // Ghi log dữ liệu đầu vào
        Log::info('addToCart - Input data:', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);
        
        try {
            $userId = Auth::id();
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            // Kiểm tra sản phẩm có tồn tại không
            $product = Product::find($request->product_id);
            if (!$product) {
                Log::error('addToCart - Product not found:', ['product_id' => $request->product_id]);
                return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
            }

            // Kiểm tra nếu sản phẩm có biến thể nhưng không chọn biến thể
            if ($product->variants()->exists() && !$request->product_variant_id) {
                return response()->json(['message' => 'Bạn phải chọn biến thể trước khi thêm vào giỏ hàng'], 400);
            }

            // Kiểm tra biến thể sản phẩm (nếu có)

            $variant = null;
            if ($request->product_variant_id) {
                $variant = ProductVariant::where('id', $request->product_variant_id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$variant) {
                    Log::error('addToCart - Variant not found:', [
                        'product_id' => $request->product_id,
                        'product_variant_id' => $request->product_variant_id
                    ]);
                    return response()->json(['message' => 'Biến thể không tồn tại'], 404);
                }
            }

            // Kiểm tra tồn kho
            $stock = $variant ? $variant->quantity : $product->quantity;
            $requestedQuantity = $request->quantity;

            // Kiểm tra nếu số lượng yêu cầu vượt quá tồn kho
            if ($requestedQuantity > $stock) {
                return response()->json([
                    'message' => "Số lượng sản phẩm không đủ, chỉ còn $stock cái.",
                ], 400);
            }

            // Tìm sản phẩm trong giỏ hàng
            $cartItem = CartItem::where([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $request->product_variant_id,
            ])->first();

            if ($cartItem) {
                // Cập nhật số lượng nếu đã có trong giỏ hàng
                $newQuantity = $cartItem->quantity + $requestedQuantity;
                if ($newQuantity > $stock) {
                    return response()->json([
                        'message' => "Chỉ có thể thêm tối đa " . ($stock - $cartItem->quantity) . " sản phẩm vào giỏ hàng.",
                    ], 400);
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                // Thêm sản phẩm mới vào giỏ hàng
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $requestedQuantity,
                ]);
            }

            // Lấy giá sản phẩm hoặc biến thể
            $price = $variant
                ? ($variant->discount_price ?? $variant->price)
                : ($product->discount_price ?? $product->price);

            return response()->json([
                'message' => 'Thêm vào giỏ hàng thành công',
                'cartItem' => $cartItem->toArray() + ['total_price' => $price * $cartItem->quantity]
            ], 201);
        } catch (\Exception $e) {
            Log::error('addToCart - Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function updateQuantity(Request $request, $id)
    {
        try {
            $cartItem = CartItem::findOrFail($id);

            // Kiểm tra sản phẩm có tồn tại
            if (!$cartItem->product) {
                Log::error('updateQuantity - Product not found for cart item: ' . $id);
                return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
            }

            // Lấy số lượng tồn kho
            $maxQuantity = 0;
            if ($cartItem->product_variant_id) {
                if (!$cartItem->productVariant) {
                    Log::error('updateQuantity - Variant not found for cart item: ' . $id);
                    return response()->json(['message' => 'Biến thể sản phẩm không tồn tại'], 404);
                }
                $maxQuantity = $cartItem->productVariant->quantity;
            } else {
                $maxQuantity = $cartItem->product->quantity;
            }

            // Lấy số lượng mới từ request
            $newQuantity = (int) $request->quantity;

            if ($newQuantity < 1) {
                return response()->json(['message' => 'Số lượng tối thiểu là 1'], 400);
            }

            if ($newQuantity > $maxQuantity) {
                return response()->json([
                    'message' => "Số lượng tối đa có thể thêm là $maxQuantity sản phẩm."
                ], 400);
            }

            // Cập nhật số lượng
            $cartItem->update(['quantity' => $newQuantity]);

            // Tính giá
            $price = 0;
            if ($cartItem->product_variant_id && $cartItem->productVariant) {
                $price = $cartItem->productVariant->discount_price ?? $cartItem->productVariant->price;
            } else {
                $price = $cartItem->product->discount_price ?? $cartItem->product->price;
            }

            return response()->json([
                'message' => 'Cập nhật số lượng thành công',
                'cartItem' => $cartItem->toArray() + ['total_price' => $price * $cartItem->quantity]
            ], 200);
        } catch (\Exception $e) {
            Log::error('updateQuantity - Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật giỏ hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeFromCart($id)
    {
        try {
            $userId = Auth::id();
            $cart = Cart::where('user_id', $userId)->first();
            
            if (!$cart) {
                return response()->json(['message' => 'Không tìm thấy giỏ hàng'], 404);
            }
            
            $variant_details = request()->input('variant_details');
            
            // Debug log
            Log::info('removeFromCart - Product ID: ' . $id);
            Log::info('removeFromCart - variant_details:', ['data' => $variant_details]);
            
            // Tìm cart item dựa vào product_id
            $query = CartItem::where('cart_id', $cart->id)
                             ->where('product_id', $id);
            
            // Nếu không có variant_details, xóa item đầu tiên của sản phẩm
            if (empty($variant_details)) {
                $cartItem = $query->first();
                
                if ($cartItem) {
                    $cartItem->delete();
                    return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'], 200);
                }
                
                return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng'], 404);
            }
            
            // Lấy tất cả cart items của sản phẩm này
            $cartItems = $query->get();
            
            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng'], 404);
            }
            
            // Nếu chỉ có một cart item và không có biến thể, xóa nó
            if ($cartItems->count() == 1 && !$cartItems[0]->variant_details) {
                $cartItems[0]->delete();
                return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'], 200);
            }
            
            // Xử lý trường hợp đơn giản: xóa item đầu tiên tìm được
            $cartItem = $cartItems->first();
            $cartItem->delete();
            return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'], 200);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa sản phẩm khỏi giỏ hàng: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm', 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clearCart()
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->first();

        if ($cart) {
            $cart->cartItems()->delete();
        }

        return response()->json(['message' => 'Đã xóa toàn bộ giỏ hàng'], 200);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng theo variant_details
     * API được gọi từ frontend thông qua axios
     */
    public function updateCartItemQuantity(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            // Log dữ liệu request đầu vào
            Log::info('updateCartItemQuantity - Input data:', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'variant_details' => $request->variant_details
            ]);

            $userId = Auth::id();
            $cart = Cart::where('user_id', $userId)->first();

            if (!$cart) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy giỏ hàng'], 404);
            }

            $productId = $request->product_id;
            $productVariantId = $request->product_variant_id;
            $newQuantity = $request->quantity;
            $variantDetails = $request->variant_details;

            // Kiểm tra sản phẩm có tồn tại không
            $product = Product::find($productId);
            if (!$product) {
                Log::error('updateCartItemQuantity - Product not found:', ['product_id' => $productId]);
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            }

            // Tìm kiếm item trong giỏ hàng
            $query = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $productId);
                        
            // Nếu có product_variant_id, thêm điều kiện tìm kiếm
            if ($productVariantId) {
                $query->where('product_variant_id', $productVariantId);
            }
            
            $cartItem = $query->first();

            // Nếu không tìm thấy item theo variant_id, thử tìm kiếm bằng cách so sánh variant_details
            if (!$cartItem && $variantDetails) {
                // Lấy tất cả các cart item của sản phẩm này
                $cartItems = CartItem::where('cart_id', $cart->id)
                                    ->where('product_id', $productId)
                                    ->get();
                
                Log::debug('updateCartItemQuantity - Searching in cart items:', [
                    'cart_items_count' => $cartItems->count(),
                    'cart_items' => $cartItems->pluck('id')->toArray()
                ]);
                
                // Duyệt qua từng cart item để so sánh variant_details
                foreach ($cartItems as $item) {
                    // Nếu item có thông tin variant_details khớp với request
                    $match = $this->compareVariantDetails($item, $variantDetails);
                    
                    Log::debug('updateCartItemQuantity - Compare result for cart item #' . $item->id . ':', [
                        'match' => $match
                    ]);
                    
                    if ($match) {
                        $cartItem = $item;
                        break;
                    }
                }
            }

            if (!$cartItem) {
                Log::warning('updateCartItemQuantity - Cart item not found:', [
                    'product_id' => $productId,
                    'variant_details' => $variantDetails,
                    'cart_id' => $cart->id
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
                ], 404);
            }

            // Ghi log thông tin cart item trước khi cập nhật
            Log::info('updateCartItemQuantity - Found cart item:', [
                'cart_item_id' => $cartItem->id,
                'old_quantity' => $cartItem->quantity,
                'new_quantity' => $newQuantity
            ]);

            // Kiểm tra biến thể nếu có
            $maxQuantity = 0;
            if ($cartItem->product_variant_id) {
                // Kiểm tra biến thể có tồn tại không
                $variant = ProductVariant::find($cartItem->product_variant_id);
                if (!$variant) {
                    Log::error('updateCartItemQuantity - Variant not found:', ['variant_id' => $cartItem->product_variant_id]);
                    return response()->json(['success' => false, 'message' => 'Biến thể sản phẩm không tồn tại'], 404);
                }
                $maxQuantity = $variant->quantity;
            } else {
                $maxQuantity = $product->quantity;
            }

            if ($newQuantity > $maxQuantity) {
                Log::warning('updateCartItemQuantity - Quantity exceeds stock:', [
                    'requested_quantity' => $newQuantity,
                    'max_quantity' => $maxQuantity
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng tối đa có thể thêm là $maxQuantity sản phẩm."
                ], 400);
            }

            // Cập nhật số lượng
            $oldQuantity = $cartItem->quantity;
            
            // Đảm bảo newQuantity là số nguyên
            // Chuyển số lượng từ request thành số nguyên theo nhiều cách
            if (is_string($request->quantity)) {
                // Nếu là chuỗi, chuyển sang số
                $newQuantity = intval(trim($request->quantity));
            } elseif (is_float($request->quantity)) {
                // Nếu là số thực, làm tròn xuống
                $newQuantity = floor($request->quantity);
            } else {
                // Mặc định
                $newQuantity = (int) $request->quantity;
            }
            
            Log::info('updateCartItemQuantity - Converting quantity:', [
                'raw_input' => $request->quantity,
                'parsed_int' => $newQuantity,
                'type_raw' => gettype($request->quantity),
                'type_parsed' => gettype($newQuantity)
            ]);
            
            // Cập nhật trực tiếp bằng SQL để tránh bất kỳ vấn đề chuyển đổi kiểu dữ liệu nào
            DB::beginTransaction();
            try {
                $directUpdate = DB::table('cart_items')
                    ->where('id', $cartItem->id)
                    ->update(['quantity' => $newQuantity]);
                
                Log::info('updateCartItemQuantity - Direct SQL update result:', [
                    'success' => $directUpdate,
                    'cart_item_id' => $cartItem->id,
                    'forced_quantity' => $newQuantity
                ]);
                
                DB::commit();
                
                // Refresh model để lấy dữ liệu mới nhất
                $cartItem->refresh();
                
                // Đảm bảo thay đổi đã được lưu
                if ($cartItem->quantity != $newQuantity) {
                    Log::warning('updateCartItemQuantity - Update failed after SQL update, trying model update');
                    $cartItem->quantity = $newQuantity;
                    $saved = $cartItem->save();
                    
                    Log::info('updateCartItemQuantity - Model update result:', [
                        'saved_successfully' => $saved
                    ]);
                    
                    $cartItem->refresh();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('updateCartItemQuantity - Direct SQL update failed:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Nếu SQL thất bại, thử cách thông thường
                $cartItem->quantity = $newQuantity;
                $saved = $cartItem->save();
                
                Log::info('updateCartItemQuantity - Fallback update result:', [
                    'saved_successfully' => $saved
                ]);
            }
            
            // Kiểm tra lại sau khi lưu
            $updatedItem = CartItem::find($cartItem->id);
            Log::info('updateCartItemQuantity - Final quantity after all updates:', [
                'expected' => $newQuantity,
                'actual' => $updatedItem->quantity,
                'difference' => $newQuantity - $updatedItem->quantity,
                'attributes' => $updatedItem->getAttributes()
            ]);

            // Tính giá
            $price = 0;
            if ($cartItem->product_variant_id) {
                $variant = ProductVariant::find($cartItem->product_variant_id);
                if ($variant) {
                    $price = $variant->discount_price ?? $variant->price;
                } else {
                    $price = $product->discount_price ?? $product->price;
                }
            } else {
                $price = $product->discount_price ?? $product->price;
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số lượng thành công',
                'data' => [
                    'cart_item' => $updatedItem,
                    'total_price' => $price * $updatedItem->quantity
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('updateCartItemQuantity - Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật giỏ hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * So sánh variant_details giữa cart item và request
     */
    private function compareVariantDetails($cartItem, $requestVariantDetails)
    {
        // Log dữ liệu đầu vào để debug
        Log::debug('compareVariantDetails - cart item variant_details:', [
            'cart_item_id' => $cartItem->id, 
            'variant_details' => $cartItem->variant_details
        ]);
        Log::debug('compareVariantDetails - request variant_details:', [
            'request_variant_details' => $requestVariantDetails
        ]);
        
        // Nếu cart item không có variant_details, trả về false
        if (!$cartItem->variant_details) {
            return false;
        }

        try {
            // Chuyển đổi variant_details của cart item sang array nếu là chuỗi JSON
            $itemVariantDetails = is_string($cartItem->variant_details) 
                ? json_decode($cartItem->variant_details, true) 
                : $cartItem->variant_details;

            // Chuyển đổi variant_details từ request sang array nếu là chuỗi JSON
            $requestVariantArray = is_string($requestVariantDetails) 
                ? json_decode($requestVariantDetails, true) 
                : $requestVariantDetails;
                
            // Log các dữ liệu đã chuyển đổi
            Log::debug('compareVariantDetails - converted data:', [
                'itemVariantDetails' => $itemVariantDetails,
                'requestVariantArray' => $requestVariantArray
            ]);
            
            // Kiểm tra nếu một trong hai không phải array
            if (!is_array($itemVariantDetails) || !is_array($requestVariantArray)) {
                return false;
            }
            
            // Nếu cấu trúc dữ liệu của request là [{name, value}, {name, value}]
            if (isset($requestVariantArray[0]) && isset($requestVariantArray[0]['name'])) {
                // Kiểm tra cấu trúc itemVariantDetails
                if (isset($itemVariantDetails[0]) && isset($itemVariantDetails[0]['name'])) {
                    // Cả hai đều có cùng cấu trúc [{name, value}, {name, value}]
                    
                    // Sắp xếp cả hai mảng theo 'name' để đảm bảo so sánh chính xác
                    usort($itemVariantDetails, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    usort($requestVariantArray, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    // So sánh mảng đã sắp xếp
                    return json_encode($itemVariantDetails) === json_encode($requestVariantArray);
                } else {
                    // itemVariantDetails có cấu trúc khác, thử chuyển đổi để so sánh
                    $convertedItemVariants = [];
                    foreach ($itemVariantDetails as $variantId => $valueId) {
                        $variant = \App\Models\Variant::find($variantId);
                        $value = \App\Models\VariantValue::find($valueId);
                        
                        if ($variant && $value) {
                            $convertedItemVariants[] = [
                                'name' => $variant->name,
                                'value' => $value->value
                            ];
                        }
                    }
                    
                    // Sắp xếp mảng đã chuyển đổi
                    usort($convertedItemVariants, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    usort($requestVariantArray, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    Log::debug('compareVariantDetails - converted item variants:', [
                        'convertedItemVariants' => $convertedItemVariants
                    ]);
                    
                    // So sánh mảng đã chuyển đổi
                    return json_encode($convertedItemVariants) === json_encode($requestVariantArray);
                }
            } else {
                // Cấu trúc dữ liệu khác, so sánh trực tiếp
                // Chuẩn hóa dữ liệu bằng cách sắp xếp các khóa
                $normalizedItem = $this->normalizeVariantDetails($itemVariantDetails);
                $normalizedRequest = $this->normalizeVariantDetails($requestVariantArray);
                
                Log::debug('compareVariantDetails - normalized data:', [
                    'normalizedItem' => $normalizedItem,
                    'normalizedRequest' => $normalizedRequest
                ]);
                
                // So sánh JSON đã chuẩn hóa
                return json_encode($normalizedItem) === json_encode($normalizedRequest);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi so sánh variant_details: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }

        return false;
    }
    
    /**
     * Chuẩn hóa variant_details để so sánh
     */
    private function normalizeVariantDetails($variantDetails)
    {
        if (!is_array($variantDetails)) {
            return $variantDetails;
        }
        
        // Sắp xếp mảng một chiều
        if (!isset($variantDetails[0]) || !is_array($variantDetails[0])) {
            ksort($variantDetails);
            return $variantDetails;
        }
        
        // Sắp xếp mảng hai chiều
        usort($variantDetails, function($a, $b) {
            if (isset($a['name']) && isset($b['name'])) {
                return strcmp($a['name'], $b['name']);
            }
            return 0;
        });
        
        return $variantDetails;
    }
}
