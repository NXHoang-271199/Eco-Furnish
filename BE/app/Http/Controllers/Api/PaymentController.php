<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function getMomoConfig()
    {
        $config = DB::table('payment_methods')
            ->where('name', 'MoMo')
            ->first();

        return $config ? json_decode($config->config, true) : null;
    }

    private function getVNPayConfig()
    {
        $config = DB::table('payment_methods')
            ->where('name', 'VNPAY')
            ->first();

        return $config ? json_decode($config->config, true) : null;
    }

    /**
     * 📌 Xử lý thanh toán (MoMo / VNPay)
     */
    public function processPayment(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:MoMo,VNPAY'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $orderId = $request->order_id;
            $paymentMethod = $request->payment_method;

            // Lấy đơn hàng chưa thanh toán
            $order = Order::with(['orderItems.product', 'orderItems.productVariant'])
                ->where('id', $orderId)
                ->where('user_id', auth()->id())
                ->where('payment_status', 0)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đơn hàng không hợp lệ hoặc đã thanh toán'
                ], 400);
            }

            // Lấy tổng tiền đơn hàng
            $totalAmount = $order->total_price;

            if ($paymentMethod === 'MoMo') {
                return $this->payWithMomo($order, $totalAmount);
            } elseif ($paymentMethod === 'VNPAY') {
                return $this->payWithVnpay($order, $totalAmount);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Phương thức thanh toán không hợp lệ'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý thanh toán',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 Thanh toán qua MoMo
     */
    private function payWithMomo($order, $totalAmount)
    {
        try {
            $config = $this->getMomoConfig();
            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'MoMo chưa được cấu hình'
                ], 400);
            }

            $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
            $requestId = time() . rand(1000, 9999);

            $requestData = [
                'partnerCode' => $config['partner_code'],
                'accessKey' => $config['access_key'],
                'requestId' => $requestId,
                'orderId' => $order->order_code,
                'amount' => $totalAmount,
                'orderInfo' => "Thanh toán đơn hàng #{$order->order_code}",
                'redirectUrl' => route('payment.callback', ['order_id' => $order->id, 'method' => 'momo']),
                'ipnUrl' => route('payment.callback', ['order_id' => $order->id, 'method' => 'momo']),
                'requestType' => 'captureWallet',
                'signature' => hash_hmac('sha256', "accessKey={$config['access_key']}&amount=$totalAmount", $config['secret_key']),
            ];

            $response = Http::post($endpoint, $requestData)->json();

            if (!empty($response['payUrl'])) {
                // Log payment attempt
                Log::info('MoMo payment initiated', [
                    'order_code' => $order->order_code,
                    'request_id' => $requestId,
                    'amount' => $totalAmount
                ]);

                return response()->json([
                    'status' => 'success',
                    'payment_url' => $response['payUrl']
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Thanh toán MoMo thất bại'
            ], 400);
        } catch (\Exception $e) {
            Log::error('MoMo payment error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý thanh toán MoMo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 Thanh toán qua VNPay
     */
    private function payWithVnpay($order, $totalAmount)
    {
        try {
            $config = $this->getVNPayConfig();
            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'VNPay chưa được cấu hình'
                ], 400);
            }

            $vnp_TmnCode = $config['tmn_code'];
            $vnp_HashSecret = $config['hash_secret'];
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $totalAmount * 100,
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => request()->ip(),
                "vnp_OrderInfo" => "Thanh toán đơn hàng #{$order->order_code}",
                "vnp_ReturnUrl" => route('payment.callback', ['order_id' => $order->id, 'method' => 'vnpay']),
                "vnp_TxnRef" => $order->order_code
            ];

            $query = http_build_query($inputData);
            $vnp_SecureHash = hash_hmac('sha512', urldecode($query), $vnp_HashSecret);
            $paymentUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

            // Log payment attempt
            Log::info('VNPay payment initiated', [
                'order_code' => $order->order_code,
                'amount' => $totalAmount
            ]);

            return response()->json([
                'status' => 'success',
                'payment_url' => $paymentUrl
            ], 200);
        } catch (\Exception $e) {
            Log::error('VNPay payment error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý thanh toán VNPay',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 Xử lý callback sau thanh toán (MoMo / VNPay)
     */
    public function paymentCallback(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $method = $request->method;

            $order = Order::where('id', $orderId)
                ->where('payment_status', 0)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đơn hàng không hợp lệ hoặc đã thanh toán'
                ], 400);
            }

            // Log callback data
            Log::info('Payment callback received', [
                'order_id' => $orderId,
                'method' => $method,
                'data' => $request->all()
            ]);

            // Kiểm tra kết quả thanh toán
            if ($method === 'momo' && $request->resultCode == 0) {
                $order->update([
                    'payment_status' => 1,
                    'order_status' => 'Đã Xác Nhận',
                    'payment_date' => now()
                ]);

                Log::info('MoMo payment successful', ['order_id' => $orderId]);
            } elseif ($method === 'vnpay' && $request->vnp_ResponseCode == '00') {
                $order->update([
                    'payment_status' => 1,
                    'order_status' => 'Đã Xác Nhận',
                    'payment_date' => now()
                ]);

                Log::info('VNPay payment successful', ['order_id' => $orderId]);
            } else {
                $order->update([
                    'payment_status' => 2,
                    'order_status' => 'Thanh Toán Thất Bại'
                ]);

                Log::warning('Payment failed', [
                    'order_id' => $orderId,
                    'method' => $method,
                    'response' => $request->all()
                ]);

                $this->restoreStockAfterTimeout($order);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật trạng thái thanh toán thành công'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý callback thanh toán',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📌 Tự động bù lại số lượng sản phẩm sau 15 phút nếu chưa thanh toán
     */
    private function restoreStockAfterTimeout($order)
    {
        try {
            dispatch(function () use ($order) {
                sleep(900); // Chờ 15 phút
                $order->refresh();

                if ($order->payment_status == 2) { // Nếu vẫn là thất bại
                    foreach ($order->orderItems as $item) {
                        if ($item->product_variant_id) {
                            $item->productVariant->increment('quantity', $item->quantity);
                        } else {
                            $item->product->increment('quantity', $item->quantity);
                        }
                    }

                    $order->update([
                        'order_status' => 'Đã Hủy',
                        'payment_status' => 2
                    ]);

                    Log::info('Order cancelled due to payment timeout', ['order_id' => $order->id]);
                }
            })->afterResponse();
        } catch (\Exception $e) {
            Log::error('Stock restoration error: ' . $e->getMessage());
        }
    }
}
