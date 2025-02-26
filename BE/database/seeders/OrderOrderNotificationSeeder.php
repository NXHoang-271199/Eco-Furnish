<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderOrderNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('order_notifications')->insert([
                'order_id' => $i, // Giả sử ID đơn hàng từ 1 đến 5
                'is_read' => false, // Mặc định thông báo chưa đọc
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
