<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        $vouchers = [
            [
                'code' => 'WELCOME10',
                'discount_percentage' => 10,
                'max_discount_amount' => 100000,
                'min_order_value' => 500000,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'is_active' => 'active',
                'usage_limit' => 100,
                'description' => 'Giảm 10% cho đơn hàng từ 500k',
            ],
            [
                'code' => 'SALE20',
                'discount_percentage' => 20,
                'max_discount_amount' => 200000,
                'min_order_value' => 1000000,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'is_active' => 'active',
                'usage_limit' => 50,
                'description' => 'Giảm 20% cho đơn hàng từ 1 triệu',
            ],
            [
                'code' => 'SUPER30',
                'discount_percentage' => 30,
                'max_discount_amount' => 500000,
                'min_order_value' => 2000000,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'is_active' => 'active',
                'usage_limit' => 25,
                'description' => 'Giảm 30% cho đơn hàng từ 2 triệu',
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}