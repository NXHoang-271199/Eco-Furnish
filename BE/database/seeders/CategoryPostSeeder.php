<?php

namespace Database\Seeders;

use App\Models\CategoryPost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryPost::insert([
            [
                'title' => 'Trang trí nội thất',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cẩm nang nội thất',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ý tưởng không gian sống',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Xu hướng thiết kế',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            
        ]);
    }
}
