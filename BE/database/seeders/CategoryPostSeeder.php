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
                'title' => 'Category 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Category 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Category 3',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
