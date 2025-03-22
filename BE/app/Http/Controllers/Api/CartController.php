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

        // Nếu không có giỏ hàng hoặc giỏ hàng không có sản phẩm thì báo trống
        if (!$cart || $cart->cartItems()->count() === 0) {
            return response()->json(['message' => 'Giỏ hàng trống'], 200);
        }


        $cartItems = $cart->cartItems()->with('product', 'productVariant')->get();
        return response()->json(['cart' => $cart, 'items' => $cartItems], 200);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (có kiểm tra số lượng tồn kho)
     */
    public function addToCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thêm vào giỏ hàng'], 401);
        }

        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        if ($request->product_variant_id) {
            // Nếu có biến thể, kiểm tra tồn kho của biến thể
            $variant = ProductVariant::find($request->product_variant_id);
            if (!$variant) {
                return response()->json(['message' => 'Biến thể không tồn tại'], 404);
            }
            $stock = $variant->quantity; // Số lượng tồn kho của biến thể
        } else {
            // Nếu không có biến thể, kiểm tra tồn kho của sản phẩm
            $stock = $product->quantity; // Số lượng tồn kho của sản phẩm
        }

        // Kiểm tra sản phẩm đã tồn tại trong giỏ hàng chưa
        $cartItem = CartItem::where([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'product_variant_id' => $request->product_variant_id,
        ])->first();

        $requestedQuantity = (int) $request->quantity; // Số lượng muốn thêm

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $requestedQuantity; // Tổng số lượng sau khi thêm

            if ($newQuantity > $stock) {
                return response()->json([
                    'message' => "Chỉ có thể thêm tối đa " . ($stock - $cartItem->quantity) . " sản phẩm vào giỏ hàng.",
                ], 400);
            }

            // Nếu đủ số lượng, cập nhật giỏ hàng
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            if ($requestedQuantity > $stock) {
                return response()->json([
                    'message' => "Số lượng sản phẩm không đủ, chỉ còn $stock cái.",
                ], 400);
            }

            // Nếu chưa có trong giỏ hàng, thêm mới
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
        $cartItem = CartItem::find($id);
        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }
        // Lấy số lượng tồn kho từ biến thể hoặc sản phẩm
        $maxQuantity = $cartItem->product_variant_id
            ? ProductVariant::find($cartItem->product_variant_id)?->quantity
            : Product::find($cartItem->product_id)?->quantity;

        if ($maxQuantity === null) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm hoặc biến thể'], 404);
        }

        // Lấy số lượng hiện tại và tính số lượng mới
        $currentQuantity = $cartItem->quantity;
        $newQuantity = $currentQuantity + $request->quantity;
        if ($newQuantity < 1 || $newQuantity > ($maxQuantity ?? 0)) {
            return response()->json([
                'message' => $newQuantity < 1
                    ? 'Số lượng tối thiểu là 1'
                    : 'Không thể vượt quá số lượng tồn kho'
            ], 400);
        }
        // Thực hiện cập nhật số lượng
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
