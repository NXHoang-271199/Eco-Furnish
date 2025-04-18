<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    // Định nghĩa các biến thể màu sắc
    private $colorVariants = [
        'WOOD' => 'Nâu gỗ',
        'WHITE' => 'Trắng',
        'BLACK' => 'Đen',
        'GRAY' => 'Xám',
        'CREAM' => 'Kem',
        'DARK_BROWN' => 'Nâu đậm',
    ];

    // Định nghĩa các biến thể kích thước
    private $sizeVariants = [
        '1.2_0.6' => '1.2m x 0.6m',
        '1.6_0.8' => '1.6m x 0.8m',
        '2.0_1.0' => '2.0m x 1.0m',
        '1.6_2.0' => '1.6m x 2.0m',
        '1.8_2.0' => '1.8m x 2.0m',
        '2.0_2.2' => '2.0m x 2.2m',
        '0.8_1.9' => '0.8m x 1.9m',
    ];

    // Định nghĩa các danh mục
    private $categories = [
        'TABLE' => 1,
        'CHAIR' => 2,
        'SOFA' => 3,
        'BED' => 4,
        'CABINET' => 5,
        'LIGHT' => 6,
        'MIRROR' => 7,
        'CARPET' => 8
    ];

    public function run()
    {
        // Sản phẩm không có biến thể
        $simpleProducts = [
            [
                'name' => 'Gương trang trí phòng khách',
                'product_code' => 'MIRROR-' . Str::random(6),
                'category_id' => $this->categories['MIRROR'],
                'price' => 1500000,
                'description' => 'Gương trang trí cao cấp, phù hợp với nhiều không gian nội thất',
                'image_thumnail' => 'products/mirror-1.jpg',
                'quantity' => 50
            ],
            [
                'name' => 'Thảm trải sàn hiện đại',
                'product_code' => 'CARPET-' . Str::random(6),
                'category_id' => $this->categories['CARPET'],
                'price' => 2000000,
                'description' => 'Thảm trải sàn chất liệu cao cấp, họa tiết hiện đại',
                'image_thumnail' => 'products/carpet-1.jpg',
                'quantity' => 30
            ],
            [
                'name' => 'Đèn treo tường trang trí',
                'product_code' => 'LIGHT-' . Str::random(6),
                'category_id' => $this->categories['LIGHT'],
                'price' => 850000,
                'description' => 'Đèn treo tường phong cách hiện đại, ánh sáng dịu nhẹ',
                'image_thumnail' => 'products/light-1.jpg',
                'quantity' => 40
            ],
        ];

        foreach ($simpleProducts as $product) {
            Product::create($product);
        }

        // Sản phẩm có biến thể
        $productsWithVariants = [
            [
                'product' => [
                    'name' => 'Bàn ăn gỗ cao cấp',
                    'product_code' => 'TABLE-' . Str::random(6),
                    'category_id' => $this->categories['TABLE'],
                    'description' => 'Bàn ăn gỗ tự nhiên, thiết kế hiện đại',
                    'image_thumnail' => 'products/table-1.jpg'
                ],
                'variants' => [
                    [
                        'sku' => 'TABLE-WOOD-1.2_0.6',
                        'price' => 5000000,
                        'quantity' => 20,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '1.2m x 0.6m'
                        ]
                    ],
                    [
                        'sku' => 'TABLE-WOOD-1.6_0.8',
                        'price' => 6000000,
                        'quantity' => 15,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '1.6m x 0.8m'
                        ]
                    ],
                    [
                        'sku' => 'TABLE-WOOD-2.0_1.0',
                        'price' => 7500000,
                        'quantity' => 10,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '2.0m x 1.0m'
                        ]
                    ],
                ]
            ],
            [
                'product' => [
                    'name' => 'Ghế sofa đơn',
                    'product_code' => 'SOFA-' . Str::random(6),
                    'category_id' => $this->categories['SOFA'],
                    'description' => 'Ghế sofa đơn phong cách hiện đại',
                    'image_thumnail' => 'products/sofa-1.jpg'
                ],
                'variants' => [
                    [
                        'sku' => 'SOFA-WHITE-1.2_0.6',
                        'price' => 3000000,
                        'quantity' => 25,
                        'variant_details' => [
                            'color' => 'Trắng',
                            'size' => '1.2m x 0.6m'
                        ]
                    ],
                    [
                        'sku' => 'SOFA-BLACK-1.2_0.6',
                        'price' => 3000000,
                        'quantity' => 25,
                        'variant_details' => [
                            'color' => 'Đen',
                            'size' => '1.2m x 0.6m'
                        ]
                    ],
                ]
            ],
            [
                'product' => [
                    'name' => 'Giường ngủ hiện đại',
                    'product_code' => 'BED-' . Str::random(6),
                    'category_id' => $this->categories['BED'],
                    'description' => 'Giường ngủ thiết kế hiện đại, chất liệu gỗ công nghiệp cao cấp',
                    'image_thumnail' => 'products/bed-1.jpg'
                ],
                'variants' => [
                    [
                        'sku' => 'BED-WOOD-0.8_1.9',
                        'price' => 8000000,
                        'quantity' => 10,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '0.8m x 1.9m'
                        ]
                    ],
                    [
                        'sku' => 'BED-WOOD-1.6_2.0',
                        'price' => 12000000,
                        'quantity' => 8,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '1.6m x 2.0m'
                        ]
                    ],
                    [
                        'sku' => 'BED-WOOD-1.8_2.0',
                        'price' => 14000000,
                        'quantity' => 5,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '1.8m x 2.0m'
                        ]
                    ],
                    [
                        'sku' => 'BED-WOOD-2.0_2.2',
                        'price' => 16000000,
                        'quantity' => 3,
                        'variant_details' => [
                            'color' => 'Nâu gỗ',
                            'size' => '2.0m x 2.2m'
                        ]
                    ],
                ]
            ],
        ];

        foreach ($productsWithVariants as $productData) {
            // Tạo sản phẩm
            $product = Product::create($productData['product']);

            // Tạo các biến thể
            foreach ($productData['variants'] as $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantData['sku'],
                    'price' => $variantData['price'],
                    'quantity' => $variantData['quantity'],
                    'variant_details' => $variantData['variant_details'],

                ]);
            }
        }
    }
}
