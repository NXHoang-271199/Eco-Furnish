<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\OrderItem;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\RefundRequest;
use App\Models\ProductVariant;
use App\Models\OrderNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\QuickOrderRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\PaymentController;

class OrderController extends Controller
{
    /**
     * 📌 1. Lấy danh sách đơn hàng của user
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            $orders = Order::with(['orderItems.product', 'orderItems.productVariant'])
                ->where('user_id', $userId)
                ->latest()
                ->paginate(10);

            // Kiểm tra nếu không có đơn hàng nào
            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bạn chưa có đơn hàng nào.'
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi lấy danh sách đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 2. Chi tiết đơn hàng
     */
    public function show($id)
    {
        try {
            $order = Order::with(['orderItems.product', 'orderItems.productVariant'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 📌 3. Tạo đơn hàng (Checkout) & Trừ số lượng sản phẩm
     */
    public function createOrder(OrderRequest $request)
    {
        $userId = Auth::id();
        $cart = Cart::with(['cartItems.product', 'cartItems.productVariant'])
            ->where('user_id', $userId)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Giỏ hàng trống'], 400);
        }

        // Lọc cartItems: Nếu có chọn -> chỉ lấy sản phẩm đã chọn, nếu không -> lấy toàn bộ giỏ hàng
        $cartItems = $cart->cartItems()
            ->when($request->cart_items, function ($query) use ($request) {
                return $query->whereIn('id', $request->cart_items);
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Không có sản phẩm hợp lệ trong đơn hàng'], 400);
        }

        DB::beginTransaction();
        try {
            // ✅ Tính tổng tiền
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $price = $item->product_variant_id
                    ? ($item->productVariant->discount_price ?? $item->productVariant->price)
                    : ($item->product->discount_price ?? $item->product->price);

                $item->total_price = $price * $item->quantity;
                $subtotal += $item->total_price;
            }

            // ✅ Kiểm tra & áp dụng mã giảm giá
            $discountAmount = 0;
            if ($request->voucher_id) {
                $voucherResponse = app(VoucherApiController::class)->checkVoucher(new Request([
                    'voucher_id' => $request->voucher_id,
                    'subtotal' => $subtotal
                ]));

                $voucherData = json_decode($voucherResponse->getContent(), true);
                if ($voucherData['status'] === 'error') {
                    throw new \Exception($voucherData['message']);
                }

                $discountAmount = $voucherData['discount_amount'];
                Voucher::where('id', $request->voucher_id)->decrement('usage_limit');
            }

            $totalPrice = max(0, $subtotal - $discountAmount);

            // ✅ Kiểm tra phương thức thanh toán
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            if (!$paymentMethod || $paymentMethod->is_connected != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Phương thức thanh toán chưa được kích hoạt'
                ], 400);
            }

            // ✅ Tạo đơn hàng
            $order = Order::create([
                'order_code' => 'ORD' . time() . rand(1000, 9999),
                'user_id' => $userId,
                'user_name' => $request->user_name,
                'user_email' => $request->user_email,
                'user_phone' => $request->user_phone,
                'user_address' => $request->user_address,
                'payment_method_id' => $request->payment_method_id,
                'payment_status' => 0, // Chưa thanh toán
                'order_status' => 'Chưa Xác Nhận',
                'total_price' => $totalPrice,
                'voucher_id' => $request->voucher_id ?? null,
                'discount_amount' => $discountAmount,
            ]);

            // ✅ Thêm sản phẩm vào order_items & cập nhật tồn kho
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id ?? null,
                    'product_name' => $item->product->name,
                    'image_url' => $item->product->image_thumnail,
                    'quantity' => $item->quantity,
                    'price' => $item->total_price / $item->quantity,
                    'total_price' => $item->total_price,
                ]);

                $productStock = $item->product_variant_id
                    ? ProductVariant::find($item->product_variant_id)
                    : Product::find($item->product_id);

                if ($productStock && $productStock->quantity >= $item->quantity) {
                    $productStock->decrement('quantity', $item->quantity);
                } else {
                    throw new \Exception("Sản phẩm {$item->product->name} không đủ số lượng trong kho");
                }
            }

            // ✅ Xóa sản phẩm đã đặt khỏi giỏ hàng hoặc xóa toàn bộ giỏ hàng nếu mua hết
            if (!$request->cart_items || count($request->cart_items) === $cart->cartItems()->count()) {
                $cart->cartItems()->delete();
            } else {
                $cart->cartItems()->whereIn('id', $cartItems->pluck('id'))->delete();
            }

            // ✅ Gửi thông báo đơn hàng
            OrderNotification::create([
                'order_id' => $order->id,
                'is_read' => false
            ]);

            if ($request->voucher_id) {
                VoucherUsage::create(['user_id' => $userId, 'voucher_id' => $request->voucher_id]);
            }

            DB::commit();

            // ✅ Xử lý thanh toán online (MoMo, VNPAY)
            if (in_array($paymentMethod->name, ['MoMo', 'VNPAY'])) {
                return app(PaymentMethodController::class)->processPayment(new Request([
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_price' => $totalPrice,
                    'payment_method' => $paymentMethod->name,
                    'payment_method_id' => $order->payment_method_id
                ]));
            }

            // ✅ Gửi email xác nhận đơn hàng nếu thanh toán tiền mặt
            if ($paymentMethod->name === 'Tiền mặt') {
                Mail::to($order->user_email)->send(new OrderConfirmationMail($order));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi đặt hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 4. Mua ngay
     */
    public function quickOrder(QuickOrderRequest $request)
    {
        $userId = Auth::id();

        DB::beginTransaction();
        try {
            // Lấy thông tin sản phẩm
            $product = Product::findOrFail($request->product_id);
            $variant = $request->product_variant_id
                ? ProductVariant::where('id', $request->product_variant_id)
                ->where('product_id', $product->id)
                ->first()
                : null;

            // Kiểm tra nếu sản phẩm có biến thể nhưng không chọn biến thể
            if ($product->variants()->exists() && !$variant) {
                return response()->json(['message' => 'Bạn phải chọn biến thể trước khi mua ngay'], 400);
            }

            // Kiểm tra tồn kho
            $stock = $variant ? $variant->quantity : $product->quantity;
            $requestedQuantity = $request->quantity;

            if ($requestedQuantity > $stock) {
                return response()->json([
                    'message' => "Số lượng sản phẩm không đủ, chỉ còn $stock cái.",
                ], 400);
            }

            // Xác định giá bán
            $price = $variant
                ? ($variant->discount_price ?? $variant->price)
                : ($product->discount_price ?? $product->price);

            // Tổng tiền trước khi áp dụng voucher
            $subtotal = $price * $requestedQuantity;
            $discountAmount = 0;

            // Kiểm tra voucher nếu có
            if ($request->voucher_id) {
                $voucherResponse = app(VoucherApiController::class)->checkVoucher(new Request([
                    'voucher_id' => $request->voucher_id,
                    'subtotal' => $subtotal
                ]));

                $voucherData = json_decode($voucherResponse->getContent(), true);
                if ($voucherData['status'] === 'error') {
                    throw new \Exception($voucherData['message']);
                }

                $discountAmount = $voucherData['discount_amount'];
                Voucher::where('id', $request->voucher_id)->decrement('usage_limit');
            }

            // Tính tổng tiền sau khi áp dụng voucher
            $totalPrice = max(0, $subtotal - $discountAmount);

            // Xử lý phương thức thanh toán
            $paymentMethodId = PaymentMethod::find($request->payment_method_id);

            if (!$paymentMethodId || $paymentMethodId->is_connected != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Phương thức thanh toán chưa được kích hoạt'
                ], 400);
            }
            $paymentMethod = $paymentMethodId->name;
            $paymentStatus = 0;

            // Tạo đơn hàng
            $order = Order::create([
                'order_code' => 'ORD' . time() . rand(1000, 9999),
                'user_id' => $userId,
                'user_name' => $request->user_name,
                'user_email' => $request->user_email,
                'user_phone' => $request->user_phone,
                'user_address' => $request->user_address,
                'payment_method_id' => $request->payment_method_id,
                'payment_status' => $paymentStatus,
                'order_status' => 'Chưa Xác Nhận',
                'total_price' => $totalPrice,
                'voucher_id' => $request->voucher_id ?? null,
                'discount_amount' => $discountAmount
            ]);

            // Thêm sản phẩm vào order_items
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $request->product_variant_id ?? null,
                'product_name' => $product->name,
                'image_url' => $product->image_thumnail,
                'quantity' => $requestedQuantity,
                'price' => $price,
                'total_price' => $subtotal,
            ]);

            // Trừ số lượng tồn kho
            if ($variant) {
                $variant->decrement('quantity', $requestedQuantity);
            } else {
                $product->decrement('quantity', $requestedQuantity);
            }

            // ✅ Thêm thông báo đơn hàng
            OrderNotification::create([
                'order_id' => $order->id,
                'is_read' => false
            ]);

            // Lưu thông tin voucher đã sử dụng
            if ($request->voucher_id) {
                VoucherUsage::create(['user_id' => $userId, 'voucher_id' => $request->voucher_id]);
            }

            DB::commit();

            // Xử lý thanh toán nếu không phải tiền mặt
            if (in_array($paymentMethod, ['MoMo', 'VNPAY'])) {
                return app(PaymentMethodController::class)->processPayment(new Request([
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_price' => $totalPrice,
                    'payment_method' => $paymentMethod,
                    'payment_method_id' => $order->payment_method_id
                ]));
            }

            // gửi mail xác nhận đơn hàng
            if ($paymentMethod === 'Tiền mặt') {
                Mail::to($order->user_email)->send(new OrderConfirmationMail($order));
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi đặt hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 5. Xác nhận đơn hàng
     */
    public function confirmOrder($orderId)
    {
        $userId = Auth::id();

        DB::beginTransaction();
        try {
            $order = Order::where('id', $orderId)
                ->where('user_id', $userId)
                ->first();

            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
            }

            if ($order->order_status !== 'Đã Giao') {
                return response()->json(['status' => 'error', 'message' => 'Chỉ có thể xác nhận đơn hàng đã giao'], 400);
            }

            // Cập nhật trạng thái đơn hàng và trạng thái thanh toán
            $order->update([
                'order_status' => 'Đã Nhận',
                'payment_status' => 1 // Cập nhật trạng thái thanh toán
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được xác nhận thành công',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xác nhận đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * 📌 6. Hoàn hàng
     */
    public function requestRefund($orderId, Request $request)
    {
        $userId = Auth::id();
        // Tìm đơn hàng của người dùng
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        // Kiểm tra nếu không tìm thấy đơn hàng
        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng'
            ], 400);
        }
        // Kiểm tra nếu trạng thái đơn hàng là 'Hoàn Hàng' hoặc 'Từ Chối Hoàn Hàng', không cho phép gửi yêu cầu hoàn hàng nữa
        if (in_array($order->order_status, ['Hoàn Hàng', 'Từ Chối Hoàn Hàng'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng này đã có yêu cầu hoàn hàng trước đó, không thể gửi lại yêu cầu'
            ], 400);
        }
        // Kiểm tra trạng thái của đơn hàng: chỉ cho phép yêu cầu hoàn hàng khi trạng thái là 'Đã Giao'
        if ($order->order_status !== 'Đã Giao') {
            return response()->json([
                'status' => 'error',
                'message' => 'Yêu cầu hoàn hàng chỉ có thể gửi khi đơn hàng đã giao'
            ], 400);
        }
        // Kiểm tra nếu đơn hàng đã có yêu cầu hoàn hàng nào đang trong trạng thái "Chờ Duyệt"
        $existingRefundRequest = RefundRequest::where('order_id', $orderId)
            ->whereIn('status', ['Chờ Duyệt', 'Đã Duyệt', 'Đã Từ Chối'])
            ->first();

        if ($existingRefundRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng này đã có yêu cầu hoàn hàng không thể gửi lại yêu cầu'
            ], 400);
        }
        // Tạo yêu cầu hoàn hàng (Lý do có thể là null)
        RefundRequest::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'reason' => $request->reason,
            'status' => 'Chờ Duyệt',  // Trạng thái mặc định là "Chờ Duyệt"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Yêu cầu hoàn hàng của bạn đã được gửi, vui lòng chờ xét duyệt'
        ], 200);
    }








    /**
     * 📌 7. Hủy đơn hàng
     */
    public function cancelOrder($orderId)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('orderItems')->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            if ($order->order_status !== 'Chưa Xác Nhận' && $order->order_status !== 'Đã Xác Nhận') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đơn hàng đã xử lý, không thể hủy'
                ], 400);
            }

            // Hoàn lại số lượng sản phẩm
            foreach ($order->orderItems as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->update(['order_status' => 'Hủy Đơn']);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã bị hủy và số lượng hàng hóa đã được hoàn lại'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi hủy đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
