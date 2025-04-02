<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Lấy tất cả các sản phẩm
        $products = Product::all();

        // Lấy tất cả người dùng
        $users = User::all();

        foreach ($products as $product) {
            // Số lượng đánh giá cho mỗi sản phẩm (giả sử từ 1 đến 5 đánh giá)
            $reviewCount = rand(1, 5);

            for ($i = 0; $i < $reviewCount; $i++) {
                // Lấy ngẫu nhiên 1 người dùng từ danh sách
                $user = $users->random();

                DB::table('reviews')->insert([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'order_id' => rand(1,5),
                    'rating' => rand(1, 5), // Đánh giá ngẫu nhiên từ 1 đến 5
                    'review_text' => 'Đánh giá sản phẩm: ' . $product->name . ' - ' . rand(1, 5) . ' sao.', // Nội dung đánh giá
                    'is_hidden' => false, // Có thể cho phép ẩn đánh giá nếu cần
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
