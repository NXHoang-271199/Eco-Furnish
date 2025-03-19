<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use App\Models\Product;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        // Tạo comments cho sản phẩm
        for ($i = 0; $i < 50; $i++) {
            Comment::insert([
                'content' => $faker->paragraph(2),
                'user_id' => $faker->randomElement($userIds),
                'product_id' => $faker->randomElement($productIds),
                'status' => $faker->randomElement(['Hiển thị', 'Ẩn']),
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
