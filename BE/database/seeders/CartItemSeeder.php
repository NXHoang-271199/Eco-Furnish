<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('cart_items')->insert([
                'cart_id' => $i,
                'product_id' => $i,
                'product_variant_id' => 1,
                'quantity' => rand(1, 5),
            ]);
        }
    }
}
