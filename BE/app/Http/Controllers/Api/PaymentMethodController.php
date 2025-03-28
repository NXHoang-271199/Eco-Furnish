<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Http\Controllers\Controller;

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
    
}
