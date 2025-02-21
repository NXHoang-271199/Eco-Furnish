<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
<<<<<<< HEAD
            CategorySeeder::class,
            VariantSeeder::class,
            VariantValueSeeder::class,
            ProductSeeder::class,
=======
            UserSeeder::class,
            PostSeeder::class,
            RoleSeeder::class
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
        ]);
    }
} 