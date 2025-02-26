<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('products')->insert([
                'product_code' => 'SP' . str_pad($i, 3, '0', STR_PAD_LEFT), // Mã sản phẩm SP001, SP002, ...
                'name' => "Sản phẩm $i", // Tên sản phẩm
                'category_id' => rand(1, 3), // ID danh mục ngẫu nhiên, giả sử có ít nhất 3 danh mục
                'image_thumnail' => "image$i.jpg", // Hình ảnh thumbnail giả
                'price' => rand(100000, 500000), // Giá sản phẩm ngẫu nhiên
                'discount_price' => rand(80000, 400000), // Giá giảm giả
                'short_description' => "Mô tả ngắn cho sản phẩm $i", // Mô tả ngắn
                'description' => "Mô tả chi tiết cho sản phẩm $i", // Mô tả chi tiết
            ]);
        }
    }
}
