<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\CategoryPost;
use App\Models\User;
use App\Models\Role;
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
        $faker = Faker::create('vi_VN');
        
        // Lấy id của role admin và staff
        $adminStaffRoleIds = Role::whereIn('slug', ['admin', 'staff'])->pluck('id')->toArray();
        
        // Lấy danh sách user có role là admin hoặc staff
        $userIds = User::whereIn('role_id', $adminStaffRoleIds)->pluck('id')->toArray();
        
        $categoryIds = CategoryPost::pluck('id')->toArray();
        
        // Danh sách các ảnh có sẵn
        $availableImages = ['post1.jpg', 'post2.jpg', 'post3.jpg', 'post4.jpg', 'post5.jpg', 'post6.jpg', 'post7.jpg', 'post8.jpg'];

        // Danh sách tiêu đề bài viết về nội thất
        $titles = [
            'Xu hướng thiết kế nội thất hiện đại năm 2024',
            'Cách trang trí phòng khách đẹp và tiện nghi',
            'Top 10 mẫu bàn ăn đẹp cho gia đình',
            'Thiết kế phòng ngủ theo phong cách tối giản',
            'Những món đồ nội thất không thể thiếu trong nhà bếp',
            'Cách chọn sofa phù hợp với không gian sống',
            'Xu hướng màu sắc trong trang trí nội thất',
            'Giải pháp nội thất thông minh cho căn hộ nhỏ',
            'Cách bố trí nội thất theo phong thủy',
            'Những mẫu kệ tivi đẹp và hiện đại',
            'Trang trí phòng làm việc tại nhà chuyên nghiệp',
            'Cách chọn rèm cửa phù hợp với từng không gian',
            'Xu hướng thiết kế ban công và sân vườn',
            'Nội thất gỗ - Sự lựa chọn hoàn hảo cho ngôi nhà',
            'Cách trang trí phòng trẻ em sáng tạo và an toàn'
        ];

        // Danh sách nội dung mẫu
        $contents = [
            'Trong những năm gần đây, xu hướng thiết kế nội thất đã có nhiều thay đổi đáng kể. Người tiêu dùng ngày càng ưa chuộng những thiết kế đơn giản, tinh tế nhưng vẫn đảm bảo công năng sử dụng. Các vật liệu tự nhiên như gỗ, đá, tre được sử dụng nhiều hơn, tạo nên không gian sống gần gũi với thiên nhiên.

            Một số xu hướng nổi bật có thể kể đến:
            1. Sử dụng màu sắc trung tính
            2. Tối giản trong thiết kế
            3. Nội thất thông minh, đa năng
            4. Vật liệu bền vững, thân thiện môi trường
            5. Không gian mở, linh hoạt

            Để tạo nên một không gian sống hoàn hảo, việc lựa chọn nội thất phù hợp là vô cùng quan trọng. Hãy tham khảo các mẫu thiết kế của chúng tôi để có những ý tưởng tuyệt vời cho ngôi nhà của bạn.',

                        'Phòng khách là không gian quan trọng nhất trong ngôi nhà, nơi diễn ra các hoạt động sinh hoạt chung của cả gia đình. Vì vậy, việc trang trí phòng khách cần được chú trọng đặc biệt.

            Một số gợi ý để có phòng khách đẹp:
            - Chọn bộ sofa phù hợp với diện tích
            - Bố trí ánh sáng hợp lý
            - Sử dụng thảm trải sàn tạo điểm nhấn
            - Trang trí tường với tranh ảnh, đèn trang trí
            - Bố trí cây xanh tạo không gian tự nhiên

            Ngoài ra, cần chú ý đến việc sắp xếp các món đồ nội thất sao cho hài hòa và thuận tiện cho việc di chuyển.',

                        'Không gian làm việc tại nhà đang trở thành xu hướng phổ biến. Để có một góc làm việc hiệu quả, bạn cần chú ý:

            1. Vị trí đặt bàn làm việc
            - Nên đặt gần cửa sổ để tận dụng ánh sáng tự nhiên
            - Tránh đặt đối diện cửa ra vào
            - Không gian yên tĩnh, tránh khu vực nhiều người qua lại

            2. Trang thiết bị cần thiết
            - Bàn làm việc phù hợp với tư thế ngồi
            - Ghế ergonomic bảo vệ sức khỏe
            - Đèn bàn có thể điều chỉnh
            - Kệ để tài liệu gọn gàng

            3. Trang trí không gian
            - Màu sắc tươi sáng, tạo cảm hứng
            - Một số cây xanh nhỏ lọc không khí
            - Bảng ghi chú hoặc lịch treo tường
            - Các vật dụng văn phòng phẩm cần thiết'
        ];

        foreach ($titles as $index => $title) {
            Post::insert([
                'title' => $title,
                'content' => $contents[array_rand($contents)],
                'user_id' => $faker->randomElement($userIds),
                'category_post_id' => $faker->randomElement($categoryIds),
                'image_thumbnail' => 'uploads/posts/' . $faker->randomElement($availableImages),
                'slug' => Str::slug($title),
                'status' => $faker->randomElement(['0', '1']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
