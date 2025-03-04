<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            PostSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
            VariantSeeder::class,
            VariantValueSeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            PostSeeder::class,
            RoleSeeder::class,
            PaymentMethodSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            OrderNotificationSeeder::class,
        ]);
    }
}
