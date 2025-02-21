<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variant;

class VariantSeeder extends Seeder
{
    public function run()
    {
        $variants = [
            [
                'name' => 'Màu sắc',        
            ],
            [
                'name' => 'Kích thước',
            ]
        ];

        foreach ($variants as $variant) {
            Variant::create($variant);
        }
    }
} 