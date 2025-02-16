<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i < 6; $i++){
            DB::table('users')->insert([
                'name' => "User $i",
                'age' => $i,
                'email' => "user$i@php.com",
                'password' => bcrypt('12345678'),
                'address' => "Address $i",
                'role_id' => 1,
                'avatar' => null
            ]);
        }
    }
}
