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
use App\Models\ProductVariant;
use App\Models\OrderNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

        DB::beginTransaction();
        try {
            // Tính tổng tiền
            $cartItems = $cart->cartItems;
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $price = $item->product_variant_id
                    ? ($item->productVariant->discount_price ?? $item->productVariant->price)
                    : ($item->product->discount_price ?? $item->product->price);

                $item->total_price = $price * $item->quantity;
                $subtotal += $item->total_price;
            }

            // Gọi checkVoucher()
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

                // 🔥 Chỉ trừ `usage_limit` khi đơn hàng đã được tạo
                Voucher::where('id', $request->voucher_id)->decrement('usage_limit');
            }

            $totalPrice = max(0, $subtotal - $discountAmount);
            $paymentMethod = PaymentMethod::find($request->payment_method_id)?->name;
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
            ]);

            // Thêm sản phẩm vào order_items & cập nhật số lượng tồn kho
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

                if ($item->product_variant_id) {
                    $productVariant = ProductVariant::find($item->product_variant_id);
                    if ($productVariant && $productVariant->quantity >= $item->quantity) {
                        $productVariant->decrement('quantity', $item->quantity);
                    } else {
                        throw new \Exception("Sản phẩm {$item->product->name} không đủ số lượng trong kho");
                    }
                } else {
                    $product = Product::find($item->product_id);
                    if ($product && $product->quantity >= $item->quantity) {
                        $product->decrement('quantity', $item->quantity);
                    } else {
                        throw new \Exception("Sản phẩm {$item->product->name} không đủ số lượng trong kho");
                    }
                }
            }

            // Xóa giỏ hàng
            $cart->cartItems()->delete();
            // ✅ Thêm thông báo đơn hàng
            OrderNotification::create([
                'order_id' => $order->id,
                'is_read' => false
            ]);

            DB::commit();
            // Xử lý thanh toán online (MoMo, VNPAY)
            if (in_array($paymentMethod, ['MoMo', 'VNPAY'])) {
                $paymentResponse = app(PaymentMethodController::class)->processPayment(new Request([
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_price' => $totalPrice,
                    'payment_method' => $paymentMethod,
                    'payment_method_id' => $order->payment_method_id
                ]));

                return $paymentResponse;
            }
            //
            if ($request->voucher_id) {
                VoucherUsage::create(['user_id' => $userId, 'voucher_id' => $request->voucher_id]);
            }

            // gửi mail xác nhận đơn hàng
            if ($paymentMethod === 'Tiền mặt') {
                Mail::to($order->user_email)->send(new OrderConfirmationMail($order, $discountAmount));
            }
            // Xử lý thanh toán nếu không phải tiền mặt
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
    public function quickOrder(Request $request)
    {
        $userId = Auth::id();

        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'user_name' => 'required|string',
            'user_email' => 'required|email',
            'user_phone' => 'required|string',
            'user_address' => 'required|string',
            'payment_method_id' => 'required|exists:payment_methods,id'
        ]);

        DB::beginTransaction();
        try {
            // Lấy thông tin sản phẩm
            $product = Product::findOrFail($request->product_id);
            $variant = $request->product_variant_id ? ProductVariant::find($request->product_variant_id) : null;

            // Xác định giá bán
            $price = $variant
                ? ($variant->discount_price ?? $variant->price)
                : ($product->discount_price ?? $product->price);

            // Kiểm tra số lượng tồn kho
            if ($variant) {
                if ($variant->quantity < $request->quantity) {
                    throw new \Exception("Số lượng sản phẩm biến thể không đủ hàng.");
                }
            } else {
                if ($product->quantity < $request->quantity) {
                    throw new \Exception("Số lượng sản phẩm không đủ hàng.");
                }
            }

            // Tổng tiền trước khi áp dụng voucher
            $subtotal = $price * $request->quantity;
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
            $paymentMethod = PaymentMethod::find($request->payment_method_id)?->name;
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
                'voucher_id' => $request->voucher_id ?? null
            ]);

            // Thêm sản phẩm vào order_items
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $request->product_id,
                'product_variant_id' => $request->product_variant_id ?? null,
                'product_name' => $product->name,
                'image_url' => $product->image_thumnail,
                'quantity' => $request->quantity,
                'price' => $price,
                'total_price' => $subtotal,
            ]);

            // Trừ số lượng tồn kho
            if ($variant) {
                $variant->decrement('quantity', $request->quantity);
            } else {
                $product->decrement('quantity', $request->quantity);
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

            // Gửi mail xác nhận đơn hàng
            Mail::to($order->user_email)->send(new OrderConfirmationMail($order, $discountAmount));

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
     * 📌 6. Hoàn hàng
     */
    public function refundOrder($orderId, Request $request)
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

            if ($order->order_status !== 'Đã Giao Hàng') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Chỉ có thể hoàn hàng khi đơn đã giao'
                ], 400);
            }

            // Nếu hoàn toàn bộ đơn hàng
            if ($request->full_refund) {
                foreach ($order->orderItems as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                    } else {
                        Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                    }
                }
            } else {
                // Hoàn từng sản phẩm theo danh sách gửi từ request
                foreach ($request->items as $itemData) {
                    $orderItem = OrderItem::where('id', $itemData['order_item_id'])->first();
                    if ($orderItem) {
                        if ($orderItem->product_variant_id) {
                            ProductVariant::where('id', $orderItem->product_variant_id)->increment('quantity', $itemData['quantity']);
                        } else {
                            Product::where('id', $orderItem->product_id)->increment('quantity', $itemData['quantity']);
                        }
                    }
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->update(['order_status' => 'Hoàn Hàng']);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã hoàn hàng thành công'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi hoàn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
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
