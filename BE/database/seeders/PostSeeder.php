<?php

namespace Database\Seeders;

use App\Models\Post;
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
        $userIds = DB::table('users')->pluck('id')->toArray();
        for ($i = 0; $i < 20; $i++) {
            $title = $faker->sentence(6);
            Post::insert([
                'title' => $title,
                'content' => $faker->paragraphs(3, true),
                'user_id' => $faker->randomElement($userIds),
                'category_post_id' => $faker->numberBetween(1, 5), // giả sử có 5 category
                'image_thumbnail' => $faker->imageUrl(640, 480, 'post'),
                'slug' => Str::slug($title),
                'status' => $faker->randomElement(['Hiển thị', 'Ẩn']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
