<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\CategoryPost;
use App\Models\User;
use App\Models\Role;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        
        // Lấy id của role admin và staff
        $adminStaffRoleIds = Role::whereIn('slug', ['admin', 'staff'])->pluck('id')->toArray();
        $userIds = User::whereIn('role_id', $adminStaffRoleIds)->pluck('id')->toArray();
        

        $categoryIds = CategoryPost::pluck('id')->toArray();

        $posts = [
            [
                'title' => 'Cách trang trí phòng khách đẹp và tiện nghi',
                'content' => 'Hướng dẫn chi tiết cách trang trí phòng khách đẹp và tiện nghi. Từ việc chọn sofa, bàn trà đến cách bố trí ánh sáng và các vật dụng trang trí.',
            ],
            [
                'title' => 'Xu hướng thiết kế phòng ngủ 2024',
                'content' => 'Các xu hướng thiết kế phòng ngủ mới nhất năm 2024. Từ màu sắc, chất liệu đến cách bố trí nội thất thông minh.',
            ],
            [
                'title' => 'Thiết kế nhà bếp hiện đại và tiện nghi',
                'content' => 'Những ý tưởng thiết kế nhà bếp hiện đại, tiện nghi và đẹp mắt. Cách bố trí các khu vực nấu nướng, lưu trữ hợp lý.',
            ],
            [
                'title' => 'Trang trí phòng làm việc tại nhà',
                'content' => 'Gợi ý trang trí phòng làm việc tại nhà chuyên nghiệp. Từ việc chọn bàn ghế, ánh sáng đến cách sắp xếp không gian.',
            ],
            [
                'title' => 'Thiết kế ban công xanh mát',
                'content' => 'Ý tưởng thiết kế ban công xanh mát với cây cảnh và nội thất ngoài trời. Tạo không gian thư giãn lý tưởng.',
            ]
        ];

        foreach ($posts as $post) {
            Post::create([
                'title' => $post['title'],
                'content' => $post['content'],
                'user_id' => $faker->randomElement($userIds),
                'category_post_id' => $faker->randomElement($categoryIds),
                'image_thumbnail' => null,
                'slug' => Str::slug($post['title']),
                'status' => $faker->randomElement(['0', '1']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
