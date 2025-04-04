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
}