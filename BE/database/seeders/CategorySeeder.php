<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ trước khi seed để đảm bảo ID đúng thứ tự
        Category::truncate();
        
        $categories = [
            ['name' => 'Bàn'],
            ['name' => 'Ghế'],
            ['name' => 'Sofa'],
            ['name' => 'Giường'],
            ['name' => 'Tủ'],
            ['name' => 'Đèn trang trí'],
            ['name' => 'Gương'],
            ['name' => 'Thảm'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}