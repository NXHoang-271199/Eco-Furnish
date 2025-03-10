<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu có
        DB::table('orders')->delete();

        // Reset auto-increment
        DB::statement('ALTER TABLE orders AUTO_INCREMENT = 1');

        for ($i = 1; $i <= 5; $i++) {
            DB::table('orders')->insert([
                'id' => $i, // Chỉ định rõ ID
                'order_code' => 'OD' . str_pad($i, 3, '0', STR_PAD_LEFT), // Mã đơn hàng OD001, OD002...
                'user_id' => 1, // Đảm bảo user_id tồn tại
                'user_name' => "Người Dùng $i",
                'user_email' => "ngdung$i@example.com",
                'user_phone' => "098765432$i",
                'user_address' => "Địa chỉ người dùng $i",
                'payment_method_id' => 1, // ID phương thức thanh toán
                'payment_status' => 1, // Thanh toán thành công
                'order_status' => 'Chưa Xác Nhận', // Trạng thái đơn hàng
                'voucher_id' => 1, // ID voucher
                'total_price' => 200000,
                'created_at' => now(),
            ]);
        }
    }
}
