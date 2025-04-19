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

        // Tạo tài khoản admin
        User::create([
            'name' => 'Admin',
            'email' => '1@gmail.com',
            'password' => Hash::make('1'),
            'role_id' => 1, // Role Admin
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
             // Tạo tài khoản staff
        User::create([
            'name' => 'Staff',
            'email' => 'staff@gmail.com',
            'password' => Hash::make(1),
            'role_id' => 2,
            'email_verified_at' => now(),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
