<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            CategoryPostSeeder::class,
            PostSeeder::class,
            VariantSeeder::class,
            VariantValueSeeder::class,
            ProductSeeder::class,
            PaymentMethodSeeder::class,
            VoucherSeeder::class,
        ]);

        // Tạo tài khoản quản trị viên
        User::create([
            'name' => 'Quản trị viên',
            'email' => 'quantrivien@gmail.com',
            'password' => Hash::make('1'),
            'role_id' => 1, // Role Admin
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tạo tài khoản nhân viên
        User::create([
            'name' => 'Nhân viên',
            'email' => 'nhanvien@gmail.com',
            'password' => Hash::make('1'),
            'role_id' => 2,
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tạo tài khoản khách hàng
        User::create([
            'name' => 'Khách hàng',
            'email' => 'khachhang@gmail.com',
            'password' => Hash::make('1'),
            'role_id' => 3,
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
