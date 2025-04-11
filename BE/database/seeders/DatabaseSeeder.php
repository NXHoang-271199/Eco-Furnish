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
            OrderSeeder::class,
            OrderItemSeeder::class,
            OrderNotificationSeeder::class,
            CommentSeeder::class,
            // TopBuyersSeeder::class,
            CartSeeder::class,
            CartItemSeeder::class,
            ReviewSeeder::class,
            WalletSeeder::class,
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
    }
}
