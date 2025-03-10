<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\CategoryPost;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id')->toArray();
        $categoryIds = CategoryPost::pluck('id')->toArray();
        for ($i = 0; $i < 5; $i++) {
            $title = $faker->sentence(6);
            Post::insert([
                'title' => $title,
                'content' => $faker->paragraphs(3, true),
                'user_id' => $faker->randomElement($userIds),
                'category_post_id' => $faker->randomElement($categoryIds), // giả sử có 5 category
                'image_thumbnail' => "https://picsum.photos/200/200?random=" . $faker->unique()->randomNumber(),
                'slug' => Str::slug($title),
                'status' => $faker->randomElement(['0', '1']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
