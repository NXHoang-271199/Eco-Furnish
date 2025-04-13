<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        for ($i = 0; $i < 5; $i++) {
            User::insert([
                'name' => $faker->name,
                'age' => $faker->numberBetween(18, 60),
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('1'),
                'address' => $faker->address,
                'role_id' => 3,
                'avatar' => $faker->randomElement($avatars),
                'email_verified_at' => $faker->dateTimeThisYear(),
                'is_active' => 1,
                'access_token' => $faker->uuid,
                'refresh_token' => $faker->uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
