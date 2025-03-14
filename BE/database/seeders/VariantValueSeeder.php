<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariantValue;

class VariantValueSeeder extends Seeder
{
    public function run()
    {
        // Giá trị cho variant Màu sắc (ID: 1)
        $colors = [
            'Nâu gỗ',
            'Trắng',
            'Đen',
            'Xám',
            'Kem',
            'Nâu đậm',
        ];

        foreach ($colors as $color) {
            VariantValue::create([
                'variant_id' => 1,
                'value' => $color,
            ]);
        }

        // Giá trị cho variant Kích thước (ID: 2)
        $sizes = [
            'Nhỏ',
            'Vừa',
            'Lớn',
        ];

        foreach ($sizes as $size) {
            VariantValue::create([
                'variant_id' => 2,
                'value' => $size,
            ]);
        }
    }
} 