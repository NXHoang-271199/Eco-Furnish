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
                'config' => json_encode([]),
                'is_connected' => false
            ],
            [
                'name' => 'VNPAY',
                'config' => json_encode([]),
                'is_connected' => false
            ],
            [
                'name' => 'Tiền mặt',
                'config' => json_encode([]),
                'is_connected' => true
            ],
            [
                'name' => 'Ví',
                'config' => json_encode([]),
                'is_connected' => true
            ]
        ]);
    }
}
