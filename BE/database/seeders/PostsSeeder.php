<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i < 6; $i++){
            DB::table('posts')->insert([
                'title' => "Title $i",
                'slug' => "slug $i",
                'image' => null,
                'img_thumbnail' => null,
                'content' => "Content $i",
                'status' => 1,
                'publish_date' => now(),
                'user_id' => $i
            ]);
        }
    }
}
