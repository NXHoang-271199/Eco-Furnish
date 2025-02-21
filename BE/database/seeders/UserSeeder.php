<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $faker = Faker::create();
        $roleIds = Role::pluck('id')->toArray();
        for ($i = 0; $i < 5; $i++) {
            User::insert([
                'name' => $faker->name,
                'age' => $faker->numberBetween(18, 60),
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password123'), // mật khẩu mặc định
                'address' => $faker->address,
                'role_id' => $faker->randomElement($roleIds),
                'avatar' => "https://picsum.photos/200/200?random=" . $faker->unique()->randomNumber(),
                'email_verified_at' => $faker->dateTimeThisYear(),
                'access_token' => $faker->uuid,
                'refresh_token' => $faker->uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
