<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\WalletTransaction;
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
     * Lấy danh sách phương thức thanh toán đã kết nối cho đơn hàng
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

    // lấy phương thức thanh toán đã kết nối cho nạp tiền vào ví
    public function getDepositMethods()
    {
        try {
            $depositMethods = PaymentMethod::where('is_connected', true)
                ->whereIn('name', ['MoMo', 'VNPAY'])
                ->get(['id', 'name', 'image']);

            return response()->json([
                'status' => true,
                'data' => $depositMethods
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Đã xảy ra lỗi khi lấy phương thức thanh toán cho nạp tiền!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // xử lý thanh toán đơn hàng
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
        $ipnUrl = $request->notify_url ?? "https://368c-42-116-147-230.ngrok-free.app/api/momo/ipn";

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
        $vnp_Returnurl = "http://localhost:5173/order-success";
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


    // xử lý thanh toán lại đơn hàng
    public function retryPayment($orderId)
    {
        // Tìm đơn hàng theo order_id và payment_status = 2
        $order = Order::where('id', $orderId)->where('payment_status', 2)->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng hoặc đơn hàng không thể thanh toán lại'], 400);
        }
        $newOrderCode = 'ORD' . time() . rand(1000, 9999);
        // Cập nhật order với order_code mới
        $order->update([
            'order_code' => $newOrderCode,
        ]);
        // Gọi đến phương thức processPayment để xử lý thanh toán lại
        return $this->processPayment(new Request([
            'order_id' => $orderId,
            'order_code' => $newOrderCode,
            'total_price' => intval($order->total_price),
            'payment_method_id' => $order->payment_method_id,
            'payment_method' => $order->paymentMethod->name // Lấy phương thức thanh toán của đơn hàng
        ]));
    }

    // xử lý nạp tiền
    public function processWalletPayment(Request $request)
    {
        $paymentMethod = $request->payment_method;
        if ($paymentMethod === 'MoMo') {
            return $this->processMoMoPaymentWallet($request);
        } elseif ($paymentMethod === 'VNPAY') {
            return $this->processVNPAYPaymentWallet($request);
        }
        return response()->json(['status' => 'error', 'message' => 'Phương thức thanh toán không hợp lệ'], 400);
    }
    private function processMoMoPaymentWallet(Request $request)
    {
        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        if (!$paymentMethod) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phương thức thanh toán'], 400);
        }

        $config = is_array($paymentMethod->config) ? $paymentMethod->config : json_decode($paymentMethod->config, true);
        if (!is_array($config)) {
            return response()->json(['status' => 'error', 'message' => 'Cấu hình thanh toán không hợp lệ'], 400);
        }

        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = $config['partner_code'];
        $accessKey = $config['access_key'];
        $secretKey = $config['secret_key'];

        $orderInfo = "Nạp tiền vào ví qua MoMo";
        $amount = $request->amount;
        $orderId = $request->wallet_code ?? time();
        $redirectUrl = $request->return_url ?? "http://localhost:5173/account/wallet/deposit-success";
        $ipnUrl = $request->notify_url ?? "https://368c-42-116-147-230.ngrok-free.app/api/momo/ipn";

        $extraData = '';

        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId=" . time() . "&requestType=payWithATM";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

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

        $response = Http::post($endpoint, $data);
        return response()->json($response->json());
    }
    private function processVNPAYPaymentWallet(Request $request)
    {
        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        if (!$paymentMethod) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phương thức thanh toán'], 400);
        }

        $config = is_array($paymentMethod->config) ? $paymentMethod->config : json_decode($paymentMethod->config, true);
        if (!is_array($config)) {
            return response()->json(['status' => 'error', 'message' => 'Cấu hình thanh toán không hợp lệ'], 400);
        }

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = $request->return_url ?? "http://localhost:5173/account/wallet/deposit-success";
        $vnp_TmnCode = $config['vnp_TmnCode'];
        $vnp_HashSecret = $config['vnp_HashSecret'];

        $vnp_TxnRef = $request->wallet_code ?? time();
        $vnp_OrderInfo = "Nạp tiền vào ví qua VNPAY";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request->amount * 100;
        $vnp_Locale = 'vn';
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
        );

        ksort($inputData);
        $query = "";
        $hashdata = "";
        $i = 0;
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
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json([
            'message' => 'success',
            'data' => $vnp_Url
        ]);
    }
    // xử lý nạp tiền lại
    public function retryDepositPayment($id)
    {
        $transaction = WalletTransaction::with('paymentMethod')->where('id', $id)
            ->where('type', 'nap_tien')
            ->where('status', 'cho_thanh_toan') // Chỉ cho phép retry nếu giao dịch trước bị huỷ
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy giao dịch hoặc giao dịch không thể thanh toán lại'
            ], 400);
        }

        // Tạo lại mã giao dịch mới
        $newWalletCode = 'NAP' . rand(100000, 999999);
        $transaction->update([
            'wallet_code' => $newWalletCode,
        ]);

        return $this->processWalletPayment(new Request([
            'transaction_id' => $id,
            'wallet_code' => $newWalletCode,
            'amount' => intval($transaction->amount),
            'payment_method_id' => $transaction->payment_method_id,
            'payment_method' => $transaction->paymentMethod->name
        ]));
    }

    // xử lý IPN
    public function handleMoMoIPN(Request $request)
    {
        try {
            $data = $request->all();

            if ($data['resultCode'] == 0) {
                $orderId = $data['orderId'];

                // Tìm đơn hàng nếu có
                $order = Order::where('order_code', $orderId)->first();
                if ($order) {
                    $order->update([
                        'payment_status' => 1
                    ]);

                    // ✅ Cập nhật status cho WalletTransaction liên quan đơn hàng
                    WalletTransaction::where('order_id', $order->id)
                        ->where('type', 'thanh_toan_don_hang')
                        ->where('status', 'cho_thanh_toan')
                        ->update(['status' => 'thanh_cong']);

                    Mail::to($order->user_email)->send(new OrderConfirmationMail($order));
                    return response()->json(['status' => 'success', 'message' => 'Thanh toán MoMo thành công']);
                }
                // Xử lý nạp ví
                $transaction = WalletTransaction::where('wallet_code', $orderId)->first();

                if (!$transaction) {
                    return response()->json(['status' => 'error', 'message' => 'Giao dịch không tồn tại'], 404);
                }

                if ($transaction->type === 'nap_tien' && $transaction->status === 'cho_thanh_toan') {
                    $transaction->update(['status' => 'thanh_cong']);

                    $wallet = Wallet::find($transaction->wallet_id);
                    if ($wallet) {
                        $wallet->increment('balance', $transaction->amount);
                    }

                    return response()->json(['status' => 'success', 'message' => 'Nạp tiền ví bằng MoMo thành công']);
                }

                return response()->json(['status' => 'error', 'message' => 'Trạng thái giao dịch không hợp lệ'], 400);
            }

            return response()->json(['status' => 'error', 'message' => 'Thanh toán thất bại'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý IPN MoMo',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function handleVNPAYIPN(Request $request)
    {
        try {
            $data = $request->query();

            if ($data['vnp_ResponseCode'] == "00") {
                $txnRef = $data['vnp_TxnRef'];

                // Tìm đơn hàng nếu có
                $order = Order::where('order_code', $txnRef)->first();
                if ($order) {
                    $order->update([
                        'payment_status' => 1
                    ]);

                    // ✅ Cập nhật status cho WalletTransaction liên quan đơn hàng
                    WalletTransaction::where('order_id', $order->id)
                        ->where('type', 'thanh_toan_don_hang')
                        ->where('status', 'cho_thanh_toan')
                        ->update(['status' => 'thanh_cong']);

                    Mail::to($order->user_email)->send(new OrderConfirmationMail($order));
                    return response()->json(['status' => 'success', 'message' => 'Thanh toán VNPAY thành công']);
                }
                // Xử lý nạp ví
                $transaction = WalletTransaction::where('wallet_code', $txnRef)->first();

                if (!$transaction) {
                    return response()->json(['status' => 'error', 'message' => 'Giao dịch không tồn tại'], 404);
                }

                if ($transaction->type === 'nap_tien' && $transaction->status === 'cho_thanh_toan') {
                    $transaction->update(['status' => 'thanh_cong']);

                    $wallet = Wallet::find($transaction->wallet_id);
                    if ($wallet) {
                        $wallet->increment('balance', $transaction->amount);
                    }

                    return response()->json(['status' => 'success', 'message' => 'Nạp tiền ví bằng VNPAY thành công']);
                }

                return response()->json(['status' => 'error', 'message' => 'Trạng thái giao dịch không hợp lệ'], 400);
            }

            return response()->json(['status' => 'error', 'message' => 'Thanh toán thất bại'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý IPN VNPAY',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
