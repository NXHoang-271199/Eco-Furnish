<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->insert([
                'name' => "Người Dùng $i", // Tên người dùng
                'age' => rand(18, 60), // Tuổi ngẫu nhiên từ 18 đến 60
                'email' => "user$i@example.com", // Email duy nhất
                'password' => bcrypt('password'), // Mật khẩu đã mã hóa
                'address' => "Địa chỉ người dùng $i", // Địa chỉ ngẫu nhiên
                'role_id' => rand(1, 3), // ID vai trò ngẫu nhiên, giả sử có ít nhất 3 vai trò
                'avatar' => "avatar$i.jpg", // Ảnh đại diện giả
                'email_verified_at' => now(), // Thời gian xác minh email
                'access_token' => Str::random(60), // Mã truy cập ngẫu nhiên
                'refresh_token' => Str::random(60), // Mã làm mới ngẫu nhiên
            ]);
        }
    }
}
