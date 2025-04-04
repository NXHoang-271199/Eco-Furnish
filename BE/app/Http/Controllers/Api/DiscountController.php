<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function verify(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0'
            ]);

            $voucher = Voucher::where('code', $request->code)
                ->where('end_date', '>', Carbon::now())
                ->where('start_date', '<=', Carbon::now())
                ->where('usage_limit', '>', 0)
                ->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn'
                ], 400);
            }

            // Kiểm tra giá trị đơn hàng tối thiểu
            if ($request->subtotal < $voucher->min_order_value) {
                return response()->json([
                    'success' => false,
                    'message' => "Đơn hàng tối thiểu phải từ " . number_format($voucher->min_order_value) . "đ"
                ], 400);
            }

            // Tính toán số tiền giảm giá
            $discountAmount = $request->subtotal * ($voucher->discount_percentage / 100);

            // Kiểm tra giới hạn số tiền giảm giá tối đa
            if ($discountAmount > $voucher->max_discount_amount) {
                $discountAmount = $voucher->max_discount_amount;
            }

            return response()->json([
                'success' => true,
                'message' => 'Mã giảm giá hợp lệ',
                'discount_amount' => $discountAmount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác thực mã giảm giá'
            ], 500);
        }
    }
} 