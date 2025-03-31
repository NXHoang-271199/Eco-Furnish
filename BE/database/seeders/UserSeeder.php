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
        // Tạo tài khoản admin
        User::create([
            'name' => 'Admin',
            'age' => 25,
            'email' => 'admin@gmail.com',
            'password' => Hash::make(1),
            'address' => 'Hà Nội, Việt Nam',
            'role_id' => Role::where('name', 'admin')->first()->id,
            'avatar' => "https://picsum.photos/200/200?random=1",
            'email_verified_at' => now(),
            'is_active' => 1,
            'access_token' => Str::uuid(),
            'refresh_token' => Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tạo tài khoản staff
        User::create([
            'name' => 'Staff',
            'age' => 22,
            'email' => 'staff@gmail.com',
            'password' => Hash::make(1),
            'address' => 'Hồ Chí Minh, Việt Nam',
            'role_id' => Role::where('name', 'staff')->first()->id,
            'avatar' => "https://picsum.photos/200/200?random=2",
            'email_verified_at' => now(),
            'is_active' => 1,
            'access_token' => Str::uuid(),
            'refresh_token' => Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
                'is_active' => 1,
                'access_token' => $faker->uuid,
                'refresh_token' => $faker->uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
