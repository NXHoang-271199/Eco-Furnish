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
                    'access_key' => 'klm05TvNBzhg7h7j',
                    'secret_key' => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
                    'partner_code' => 'MOMOBKUN20180529',
                ]),
                'is_connected' => true
            ],
            [
                'name' => 'VNPAY',
                'config' => json_encode([
                    'vnp_TmnCode' => 'ESX7PR2Z',
                    'vnp_HashSecret' => 'XXVMZ29XCOUF3IO5V971AM5JRSCDT9AG',
                ]),
                'is_connected' => true
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
