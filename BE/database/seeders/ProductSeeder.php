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
        'WOOD' => ['id' => 1, 'value_id' => 1], // Nâu gỗ
        'WHITE' => ['id' => 1, 'value_id' => 2], // Trắng
        'BLACK' => ['id' => 1, 'value_id' => 3], // Đen
        'GRAY' => ['id' => 1, 'value_id' => 4], // Xám
        'CREAM' => ['id' => 1, 'value_id' => 5], // Kem
        'DARK_BROWN' => ['id' => 1, 'value_id' => 6], // Nâu đậm
    ];

    // Định nghĩa các biến thể kích thước
    private $sizeVariants = [
        'SMALL' => ['id' => 2, 'value_id' => 1], // Nhỏ
        'MEDIUM' => ['id' => 2, 'value_id' => 2], // Vừa
        'LARGE' => ['id' => 2, 'value_id' => 3], // Lớn
        'XL' => ['id' => 2, 'value_id' => 4], // XL
        'SINGLE' => ['id' => 2, 'value_id' => 5], // 1 người
        'DOUBLE' => ['id' => 2, 'value_id' => 6], // 2 người
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
            ],
            [
                'name' => 'Thảm trải sàn hiện đại',
                'product_code' => 'CARPET-' . Str::random(6),
                'category_id' => $this->categories['CARPET'],
                'price' => 2000000,
                'description' => 'Thảm trải sàn chất liệu cao cấp, họa tiết hiện đại',
                'image_thumnail' => 'products/carpet-1.jpg',
            ],
            [
                'name' => 'Đèn treo tường trang trí',
                'product_code' => 'LIGHT-' . Str::random(6),
                'category_id' => $this->categories['LIGHT'],
                'price' => 850000,
                'description' => 'Đèn treo tường phong cách hiện đại, ánh sáng dịu nhẹ',
                'image_thumnail' => 'products/light-1.jpg',
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
                    'price' => 5000000,
                    'description' => 'Bàn ăn gỗ tự nhiên, thiết kế hiện đại',
                    'image_thumnail' => 'products/table-1.jpg',
                ],
                'variants' => [
                    [
                        'sku' => 'TABLE-WOOD-S',
                        'price' => 5000000,
                        'combinations' => [
                            $this->colorVariants['WOOD'],
                            $this->sizeVariants['SMALL']
                        ]
                    ],
                    [
                        'sku' => 'TABLE-WOOD-M',
                        'price' => 6000000,
                        'combinations' => [
                            $this->colorVariants['WOOD'],
                            $this->sizeVariants['MEDIUM']
                        ]
                    ],
                ]
            ],
            [
                'product' => [
                    'name' => 'Ghế sofa đơn',
                    'product_code' => 'SOFA-' . Str::random(6),
                    'category_id' => $this->categories['SOFA'],
                    'price' => 3000000,
                    'description' => 'Ghế sofa đơn phong cách hiện đại',
                    'image_thumnail' => 'products/sofa-1.jpg',
                ],
                'variants' => [
                    [
                        'sku' => 'SOFA-WHITE-SINGLE',
                        'price' => 3000000,
                        'combinations' => [
                            $this->colorVariants['WHITE'],
                            $this->sizeVariants['SINGLE']
                        ]
                    ],
                    [
                        'sku' => 'SOFA-BLACK-SINGLE',
                        'price' => 3000000,
                        'combinations' => [
                            $this->colorVariants['BLACK'],
                            $this->sizeVariants['SINGLE']
                        ]
                    ],
                ]
            ],
            [
                'product' => [
                    'name' => 'Giường ngủ hiện đại',
                    'product_code' => 'BED-' . Str::random(6),
                    'category_id' => $this->categories['BED'],
                    'price' => 8000000,
                    'description' => 'Giường ngủ thiết kế hiện đại, chất liệu gỗ công nghiệp cao cấp',
                    'image_thumnail' => 'products/bed-1.jpg',
                ],
                'variants' => [
                    [
                        'sku' => 'BED-WOOD-SINGLE',
                        'price' => 8000000,
                        'combinations' => [
                            $this->colorVariants['WOOD'],
                            $this->sizeVariants['SINGLE']
                        ]
                    ],
                    [
                        'sku' => 'BED-WOOD-DOUBLE',
                        'price' => 12000000,
                        'combinations' => [
                            $this->colorVariants['WOOD'],
                            $this->sizeVariants['DOUBLE']
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
                foreach ($variantData['combinations'] as $combination) {
                    $variant = new ProductVariant([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'status' => true,
                        'variant_id' => $combination['id'],
                        'variant_value_id' => $combination['value_id']
                    ]);
                    
                    $product->variants()->save($variant);
                }
            }
        }
    }
} 