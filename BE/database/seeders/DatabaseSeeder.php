<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
        ]);
    }
}
