<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('order_items')->insert([
                'order_id' => $i, // ID đơn hàng từ 1 đến 5
                'product_id' => $i, // ID sản phẩm từ 1 đến 5
                'product_name' => "Sản phẩm $i",
                'image_url' => $i,
                'quantity' => 2, // Số lượng sản phẩm
                'price' => 500000, // Giá sản phẩm
                'total_price' => 1000000, // Tổng tiền = giá * số lượng
                'product_variant_id' => 1, // ID biến thể sản phẩm (nếu có)
            ]);
        }
    }
}
