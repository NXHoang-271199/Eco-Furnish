<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;

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
} 