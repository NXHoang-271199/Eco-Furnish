<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $product = Product::findOrFail($request->product_id);

        if ($product->variants()->exists() && !$request->product_variant_id) {
            return response()->json(['message' => 'Bạn phải chọn biến thể trước khi thêm vào giỏ hàng'], 400);
        }

        if ($request->product_variant_id) {
            $variant = ProductVariant::where('id', $request->product_variant_id)
                ->where('product_id', $product->id)
                ->first();

            if (!$variant) {
                return response()->json(['message' => 'Biến thể không tồn tại trong sản phẩm'], 400);
            }

            $stock = $variant->quantity;
        } else {
            $stock = $product->quantity;
        }

        $cartItem = CartItem::where([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
        ])->first();

        $requestedQuantity = $request->quantity;

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $requestedQuantity;

            if ($newQuantity > $stock) {
                return response()->json([
                    'message' => "Chỉ có thể thêm tối đa " . ($stock - $cartItem->quantity) . " sản phẩm vào giỏ hàng.",
                ], 400);
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            if ($requestedQuantity > $stock) {
                return response()->json([
                    'message' => "Số lượng sản phẩm không đủ, chỉ còn $stock cái.",
                ], 400);
            }

            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $requestedQuantity,
            ]);
        }

        return response()->json(['message' => 'Thêm vào giỏ hàng thành công', 'cartItem' => $cartItem], 201);
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

        if ($newQuantity < 1 || $newQuantity > $maxQuantity) {
            return response()->json([
                'message' => $newQuantity < 1
                    ? 'Số lượng tối thiểu là 1'
                    : 'Không thể vượt quá số lượng tồn kho'
            ], 400);
        }

        // Cập nhật số lượng
        $cartItem->update(["quantity" => $newQuantity]);

        return response()->json(['message' => 'Cập nhật số lượng thành công', 'cartItem' => $cartItem], 200);
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
