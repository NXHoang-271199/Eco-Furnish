<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Bàn'],
            ['name' => 'Ghế'],
            ['name' => 'Tủ'],
            ['name' => 'Giường'],
            ['name' => 'Kệ sách'],
            ['name' => 'Đèn trang trí'],
            ['name' => 'Gương'],
            ['name' => 'Thảm'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 