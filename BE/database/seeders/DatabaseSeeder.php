<?php

namespace Database\Seeders;

<<<<<<< HEAD
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\OrderItem;
=======
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
<<<<<<< HEAD
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            PaymentMethodSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
        ]);
    }

}
=======
        $this->call([
            CategorySeeder::class,
            VariantSeeder::class,
            VariantValueSeeder::class,
            ProductSeeder::class,
        ]);
    }
} 
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
