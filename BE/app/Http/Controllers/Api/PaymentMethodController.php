<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PaymentMethodController extends Controller
{
    /**
     * Lấy danh sách phương thức thanh toán đã kết nối
     */
    public function index()
    {
        try {
            $paymentMethods = PaymentMethod::where('is_connected', true)->get(['id', 'name', 'image']);

            return response()->json([
                'status' => true,
                'data' => $paymentMethods
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách phương thức thanh toán!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function processPayment(Request $request)
    {
        $paymentMethod = $request->payment_method;
        if ($paymentMethod === 'MoMo') {
            return $this->processMoMoPayment($request);
        } elseif ($paymentMethod === 'VNPAY') {
            return $this->processVNPAYPayment($request);
        }
        return response()->json(['status' => 'error', 'message' => 'Phương thức thanh toán không hợp lệ'], 400);
    }

    private function processMoMoPayment(Request $request)
    {
        // Lấy thông tin phương thức thanh toán từ DB
        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        if (!$paymentMethod) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phương thức thanh toán'], 400);
        }
        // Kiểm tra và giải mã JSON
        $config = is_array($paymentMethod->config) ? $paymentMethod->config : json_decode($paymentMethod->config, true);
        if (!is_array($config)) {
            return response()->json(['status' => 'error', 'message' => 'Cấu hình thanh toán không hợp lệ'], 400);
        }
        // ✅ Định nghĩa giá trị mặc định
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = $config['partner_code'];
        $accessKey = $config['access_key'];
        $secretKey = $config['secret_key'];
        $orderInfo = "Thanh toán qua ATM MoMo";
        $amount = $request->total_price;
        $orderId = time();
        $redirectUrl = "http://localhost:3000/order-success";
        $ipnUrl = "https://f5d9-42-119-190-38.ngrok-free.app/api/momo/ipn";
        $extraData = "";
        // ✅ Nếu có dữ liệu từ request thì ghi đè giá trị mặc định
        if ($request->has('order_code')) {
            $orderId = $request->order_code;
        }
        if ($request->has('return_url')) {
            $redirectUrl = $request->return_url;
        }
        if ($request->has('notify_url')) {
            $ipnUrl = $request->notify_url;
        }
        // ✅ Tạo chữ ký SHA256
        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId=" . time() . "&requestType=payWithATM";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        // ✅ Tạo dữ liệu gửi đi
        $data = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => time(),
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'requestType' => 'payWithATM',
            'extraData' => $extraData,
            'signature' => $signature
        ];
        // ✅ Gửi request đến MoMo
        $response = Http::post($endpoint, $data);
        return response()->json($response->json());
    }

    public function handleMoMoIPN(Request $request)
    {
        try {
            $data = $request->all();
            // Kiểm tra trạng thái thanh toán từ MoMo
            if ($data['resultCode'] == 0) { // 0 = Thanh toán thành công
                $order = Order::where('order_code', $data['orderId'])->first();

                if (!$order) {
                    return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
                }

                // Cập nhật trạng thái đơn hàng và thanh toán
                $order->update([
                    'payment_status' => 1,
                    'order_status' => 'Đã Xác Nhận',
                ]);
                // Gửi email xác nhận đơn hàng
                Mail::to($order->user_email)->send(new OrderConfirmationMail($order, 0));
                return response()->json(['status' => 'success', 'message' => 'Thanh toán MoMo thành công'], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Thanh toán thất bại'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý IPN MoMo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
