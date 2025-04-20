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
            '1.2m x 0.6m', // Phù hợp cho bàn nhỏ, ghế
            '1.6m x 0.8m', // Phù hợp cho bàn ăn trung bình
            '2.0m x 1.0m', // Phù hợp cho bàn lớn
            '1.6m x 2.0m', // Phù hợp cho giường đôi
            '1.8m x 2.0m', // Phù hợp cho giường lớn hơn
            '2.0m x 2.2m', // Phù hợp cho giường king size
            '0.8m x 1.9m', // Phù hợp cho giường đơn
        ];

        foreach ($sizes as $size) {
            VariantValue::create([
                'variant_id' => 2,
                'value' => $size,
            ]);
        }
    }
}