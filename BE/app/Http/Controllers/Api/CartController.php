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
            $price = $item->product_variant_id
                ? ($item->productVariant->discount_price ?? $item->productVariant->price)
                : ($item->product->discount_price ?? $item->product->price);

            $item->total_price = $price * $item->quantity;
            return $item;
        });

        // Tính tổng tiền cả giỏ hàng
        $totalCartPrice = $cartItems->sum('total_price');

        return response()->json([
            'cart' => $cart,
            'items' => $cartItems,
            'total_cart_price' => $totalCartPrice,
        ], 200);
    }


    /**
     * Thêm sản phẩm vào giỏ hàng (có kiểm tra số lượng tồn kho)
     */
    public function addToCart(Request $request)
    {
        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($request->product_id);
        if (!$product) {
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
    }


    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function updateQuantity(Request $request, $id)
    {
        $cartItem = CartItem::findOrFail($id);

        // Lấy số lượng tồn kho
        $maxQuantity = $cartItem->product_variant_id
            ? $cartItem->productVariant->quantity
            : $cartItem->product->quantity;

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
        $price = $cartItem->product_variant_id
            ? ($cartItem->productVariant->discount_price ?? $cartItem->productVariant->price)
            : ($cartItem->product->discount_price ?? $cartItem->product->price);

        return response()->json([
            'message' => 'Cập nhật số lượng thành công',
            'cartItem' => $cartItem->toArray() + ['total_price' => $price * $cartItem->quantity]
        ], 200);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeFromCart($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();
        return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'], 200);
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
    // public function updateCartItemQuantity(Request $request)
    // {
    //     // Validate request
    //     $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1',
    //     ]);

    //     $userId = Auth::id();
    //     $cart = Cart::where('user_id', $userId)->first();

    //     if (!$cart) {
    //         return response()->json(['success' => false, 'message' => 'Không tìm thấy giỏ hàng'], 404);
    //     }

    //     $productId = $request->product_id;
    //     $productVariantId = $request->product_variant_id;
    //     $newQuantity = $request->quantity;
    //     $variantDetails = $request->variant_details;

    //     // Tìm kiếm item trong giỏ hàng
    //     $query = CartItem::where('cart_id', $cart->id)
    //                      ->where('product_id', $productId);

    //     // Nếu có product_variant_id, thêm điều kiện tìm kiếm
    //     if ($productVariantId) {
    //         $query->where('product_variant_id', $productVariantId);
    //     }

    //     $cartItem = $query->first();

    //     // Nếu không tìm thấy item theo variant_id, thử tìm kiếm bằng cách so sánh variant_details
    //     if (!$cartItem && $variantDetails) {
    //         // Lấy tất cả các cart item của sản phẩm này
    //         $cartItems = CartItem::where('cart_id', $cart->id)
    //                             ->where('product_id', $productId)
    //                             ->get();

    //         // Duyệt qua từng cart item để so sánh variant_details
    //         foreach ($cartItems as $item) {
    //             // Nếu item có thông tin variant_details khớp với request
    //             if ($this->compareVariantDetails($item, $variantDetails)) {
    //                 $cartItem = $item;
    //                 break;
    //             }
    //         }
    //     }

    //     if (!$cartItem) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
    //         ], 404);
    //     }

    //     // Kiểm tra tồn kho
    //     $maxQuantity = $cartItem->product_variant_id
    //         ? $cartItem->productVariant->quantity
    //         : $cartItem->product->quantity;

    //     if ($newQuantity > $maxQuantity) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Số lượng tối đa có thể thêm là $maxQuantity sản phẩm."
    //         ], 400);
    //     }

    //     // Cập nhật số lượng
    //     $cartItem->quantity = $newQuantity;
    //     $cartItem->save();

    //     // Tính giá
    //     $price = $cartItem->product_variant_id
    //         ? ($cartItem->productVariant->discount_price ?? $cartItem->productVariant->price)
    //         : ($cartItem->product->discount_price ?? $cartItem->product->price);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Cập nhật số lượng thành công',
    //         'data' => [
    //             'cart_item' => $cartItem,
    //             'total_price' => $price * $cartItem->quantity
    //         ]
    //     ], 200);
    // }

    /**
     * So sánh variant_details giữa cart item và request
     */
    private function compareVariantDetails($cartItem, $requestVariantDetails)
    {
        // Nếu cart item không có variant_details hoặc không phải dạng JSON, trả về false
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

            // So sánh các phần tử chính
            if (is_array($itemVariantDetails) && is_array($requestVariantArray)) {
                // So sánh đơn giản theo cấu trúc
                return json_encode(array_map('ksort', $itemVariantDetails)) ===
                       json_encode(array_map('ksort', $requestVariantArray));
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi so sánh variant_details: ' . $e->getMessage());
            return false;
        }

        return false;
    }
}
