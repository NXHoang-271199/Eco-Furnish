<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            [
                'name' => 'MoMo',
                'config' => json_encode([
                    'partner_code' => 'MOMOXXXX',
                    'access_key' => 'YOUR_ACCESS_KEY',
                    'secret_key' => 'YOUR_SECRET_KEY',
                    'endpoint_url' => 'https://sandbox.momodev.vn/gw_payment/transactionProcessor'
                ]),
                'is_connected' => false
            ],
            [
                'name' => 'VNPAY',
                'config' => json_encode([
                    'vnp_tmn_code' => 'YOUR_TMN_CODE',
                    'vnp_hash_secret' => 'YOUR_HASH_SECRET',
                    'vnp_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'
                ]),
                'is_connected' => false
            ],
            [
                'name' => 'Tiền mặt',
                'config' => json_encode([]),
                'is_connected' => true
            ]
        ]);
    }
}
