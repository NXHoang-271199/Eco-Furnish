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
    public function show($id)
{
    try {
        // Tìm phương thức thanh toán theo id và chỉ lấy các trường cần thiết
        $paymentMethod = PaymentMethod::where('id', $id)
                                      ->where('is_connected', true)
                                      ->first(['id', 'name', 'image']);

        // Kiểm tra nếu không tìm thấy phương thức thanh toán
        if (!$paymentMethod) {
            return response()->json([
                'status' => false,
                'message' => 'Phương thức thanh toán không tồn tại hoặc không được kết nối!'
            ], 404);
        }

        // Trả về kết quả dưới dạng JSON
        return response()->json([
            'status' => true,
            'data' => $paymentMethod
        ], 200);
    } catch (\Exception $e) {
        // Xử lý lỗi và trả về thông báo lỗi
        return response()->json([
            'status' => false,
            'message' => 'Đã xảy ra lỗi khi lấy thông tin phương thức thanh toán!',
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

    // xử lý momo
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
        $orderId = $request->order_code ?? time();
        $redirectUrl = $request->return_url ?? "http://localhost:5173/order-success";
        $ipnUrl = $request->notify_url ?? "https://52a8-42-1-77-241.ngrok-free.app/api/momo/ipn";

        $extraData = '';

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

            if ($data['resultCode'] == 0) { // Thanh toán thành công
                $order = Order::where('order_code', $data['orderId'])->first();

                if (!$order) {
                    return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
                }

                // Cập nhật trạng thái đơn hàng và discount_amount nếu có
                $order->update([
                    'payment_status' => 1,
                    'order_status' => 'Đã Xác Nhận',
                ]);

                // Gửi email xác nhận đơn hàng với discount_amount từ order
                Mail::to($order->user_email)->send(new OrderConfirmationMail($order));

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


    // xử lý vnpay
    public function processVNPAYPayment(Request $request)
    {
        // Lấy thông tin phương thức thanh toán từ DB
        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        if (!$paymentMethod) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phương thức thanh toán'], 400);
        }

        // Kiểm tra và giải mã JSON config
        $config = is_array($paymentMethod->config) ? $paymentMethod->config : json_decode($paymentMethod->config, true);
        if (!is_array($config)) {
            return response()->json(['status' => 'error', 'message' => 'Cấu hình thanh toán không hợp lệ'], 400);
        }
        //   $data = $request->all();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost:5174/order-success";
        $vnp_TmnCode = $config['vnp_TmnCode']; //Mã website tại VNPAY
        $vnp_HashSecret = $config['vnp_HashSecret']; //Chuỗi bí mật

        $vnp_TxnRef = $request->order_code;
        $vnp_OrderInfo = "Thanh toán đơn hàng #$vnp_TxnRef qua VNPAY";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request->total_price * 100;
        $vnp_Locale = 'vn';
        // $vnp_BankCode = 'NCB';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            // "vnp_Url_IPN" => $vnp_IpnUrl

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        return response()->json([
            'message' => 'success',
            'data' => $vnp_Url
        ]);
    }
    public function handleVNPAYIPN(Request $request)
    {
        try {
            $data = $request->query(); // Lấy dữ liệu từ query string

            if ($data['vnp_ResponseCode'] == "00") {
                // Tìm đơn hàng bằng orderCode
                $order = Order::where('order_code', $data['vnp_TxnRef'])->first();
                if (!$order) {
                    return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
                }

                // Cập nhật trạng thái đơn hàng
                $order->update([
                    'payment_status' => 1,
                    'order_status' => 'Đã Xác Nhận',
                ]);

                // Gửi email xác nhận với discount_amount từ order
                Mail::to($order->user_email)->send(new OrderConfirmationMail($order));

                return response()->json(['status' => 'success', 'message' => 'Thanh toán VNPAY thành công'], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Thanh toán thất bại'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý IPN VNPAY',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
