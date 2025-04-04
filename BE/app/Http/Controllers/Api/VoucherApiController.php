<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VoucherApiController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::where('end_date', '>', Carbon::now())
            ->where('start_date', '<=', Carbon::now())
            ->where('usage_limit', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'discount_percentage' => $voucher->discount_percentage,
                    'max_discount_amount' => $voucher->max_discount_amount,
                    'min_order_value' => $voucher->min_order_value,
                    'end_date' => $voucher->end_date,
                    'remaining_uses' => $voucher->usage_limit,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $vouchers
        ]);
    }

    public function show($code)
    {
        $voucher = Voucher::where('code', $code)
            ->where('end_date', '>', Carbon::now())
            ->where('start_date', '<=', Carbon::now())
            ->where('usage_limit', '>', 0)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'discount_percentage' => $voucher->discount_percentage,
                'max_discount_amount' => $voucher->max_discount_amount,
                'min_order_value' => $voucher->min_order_value,
                'end_date' => $voucher->end_date,
                'remaining_uses' => $voucher->usage_limit,
            ]
        ]);
    }
    public function checkVoucher(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$request->has('voucher_code') || !$request->has('subtotal')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Thiếu thông tin mã voucher hoặc giá trị đơn hàng!'
                ], 400);
            }

            $subtotal = $request->subtotal;
            $voucherCode = $request->voucher_code;

            // Tìm voucher theo mã code
            $voucher = Voucher::where('code', $voucherCode)
                ->where('is_active', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('usage_limit', '>', 0)
                ->first();
            if (!$voucher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Voucher không hợp lệ hoặc đã hết hạn!'
                ], 400);
            }

            if ($subtotal < $voucher->min_order_value) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Giá trị đơn hàng chưa đủ để sử dụng voucher!'
                ], 400);
            }

            // Kiểm tra user đã dùng voucher chưa
            $usedVoucher = VoucherUsage::where('user_id', $userId)
                ->where('voucher_id', $voucher->id)
                ->exists();

            if ($usedVoucher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn đã sử dụng voucher này rồi!'
                ], 400);
            }

            // Tính số tiền giảm giá
            $discountAmount = min($subtotal * ($voucher->discount_percentage / 100), $voucher->max_discount_amount);

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher hợp lệ!',
                'discount_amount' => $discountAmount,
                'voucher_id' => $voucher->id // FE cần dùng khi đặt hàng
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hệ thống, vui lòng thử lại sau!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
