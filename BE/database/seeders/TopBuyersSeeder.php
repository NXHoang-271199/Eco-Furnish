<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Schema;

class TopBuyersSeeder extends Seeder
{
    /**
     * Tạo dữ liệu người mua hàng nhiều nhất cho dashboard
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // Tạo một số người dùng với vai trò khách hàng
        // Lấy role_id của customer hoặc vai trò không phải admin
        $roleCustomerId = DB::table('roles')->where('id', '!=', 1)->first()->id ?? 2;

        // Tạm thời tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Xóa dữ liệu cũ
        OrderItem::query()->delete();
        Order::query()->delete();

        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Tạo danh sách người dùng mua hàng (nếu chưa có)
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'role_id' => $roleCustomerId,
                'is_active' => true,
                'address' => substr($faker->address, 0, 100),
            ]);
            $users[] = $user;
        }

        // Lấy danh sách sản phẩm để tạo đơn hàng
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->info('Không có sản phẩm nào để tạo đơn hàng mẫu! Vui lòng chạy ProductSeeder trước.');
            return;
        }

        // Lấy các biến thể sản phẩm nếu có
        $productVariantExists = Schema::hasTable('product_variants');

        // Tạo đơn hàng và các mặt hàng trong đơn hàng
        $paymentMethods = DB::table('payment_methods')->pluck('id')->toArray();

        if (empty($paymentMethods)) {
            $this->command->info('Không có phương thức thanh toán! Vui lòng chạy PaymentMethodSeeder trước.');
            return;
        }

        // Lấy một số voucher_id nếu có (để tạo đơn hàng có voucher)
        $voucherIds = DB::table('vouchers')->pluck('id')->toArray();

        // Tạo voucher mặc định nếu không có voucher nào
        $defaultVoucherId = 1; // Mặc định ID voucher là 1
        if (empty($voucherIds) && Schema::hasTable('vouchers')) {
            // Tạo voucher mẫu nếu bảng vouchers tồn tại nhưng không có dữ liệu
            $defaultVoucherId = DB::table('vouchers')->insertGetId([
                'code' => 'DEFAULT',
                'discount' => 10,
                'max_uses' => 100,
                'used' => 0,
                'min_order_amount' => 100000,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'description' => 'Voucher mặc định cho seeder',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $voucherIds = [$defaultVoucherId];
        }

        // Order status enum values theo migration
        $orderStatusValues = [
            'Chưa Xác Nhận',
            'Đã Xác Nhận',
            'Đang Chuẩn Bị Hàng',
            'Đang Giao',
            'Đã Giao',
            'Đã Nhận',
            'Thành Công',
            'Hoàn Hàng',
            'Hủy Đơn'
        ];

        // Ánh xạ trạng thái thanh toán (0: unpaid, 1: paid)
        $paymentStatusMap = [0, 1];

        foreach ($users as $index => $user) {
            // Số lượng đơn hàng giảm dần theo thứ tự người dùng
            $orderCount = max(5, 30 - ($index * 2));

            for ($i = 0; $i < $orderCount; $i++) {
                // Chọn random trạng thái
                $orderStatus = $faker->randomElement($orderStatusValues);
                $paymentStatus = $faker->randomElement($paymentStatusMap);

                // Tạo số điện thoại với định dạng ngắn hơn để tránh lỗi (tối đa 15 ký tự)
                $shortPhone = '0' . $faker->numberBetween(9, 9) . $faker->randomNumber(8, true);

                // Tính tổng giá
                $totalPrice = $faker->randomFloat(2, 100000, 9999999);

                // Chọn một voucher ID từ danh sách, luôn đảm bảo có giá trị
                $voucher_id = !empty($voucherIds) ? $faker->randomElement($voucherIds) : $defaultVoucherId;

                // Thay vì sử dụng Order::create, sử dụng DB::table để thêm trực tiếp
                $orderData = [
                    'order_code' => 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'user_id' => $user->id,
                    'user_name' => substr($user->name, 0, 50),
                    'user_email' => $user->email,
                    'user_phone' => $shortPhone,
                    'user_address' => substr($user->address, 0, 200), // Giới hạn độ dài địa chỉ
                    'payment_method_id' => $faker->randomElement($paymentMethods),
                    'payment_status' => $paymentStatus,
                    'order_status' => $orderStatus,
                    'total_price' => $totalPrice,
                    'voucher_id' => $voucher_id,
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => now(),
                ];

                $orderId = DB::table('orders')->insertGetId($orderData);

                // Tạo sản phẩm trong đơn hàng
                $itemCount = $faker->numberBetween(1, 5);
                $orderTotal = 0;

                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $faker->randomElement($products);
                    $quantity = $faker->numberBetween(1, 3);
                    $price = $product->price;
                    $totalItemPrice = $price * $quantity;

                    // Tạo default image URL nếu không có hình ảnh
                    $imageUrl = '/assets/images/products/default.jpg';
                    if (isset($product->images) && is_array($product->images) && count($product->images) > 0) {
                        $imageUrl = $product->images[0] ?? $imageUrl;
                    }

                    // Sử dụng insert trực tiếp thay vì OrderItem::create
                    $orderItemData = [
                        'order_id' => $orderId,
                        'product_id' => $product->id,
                        'product_name' => substr($product->name ?? 'Sản phẩm #'.$product->id, 0, 255),
                        'image_url' => $imageUrl,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total_price' => $totalItemPrice,
                        'created_at' => $orderData['created_at'],
                        'updated_at' => $orderData['updated_at'],
                    ];

                    // Thêm ProductVariant nếu bắt buộc
                    if ($productVariantExists) {
                        $orderItemData['product_variant_id'] = 1; // Mặc định ID variant là 1
                    }

                    DB::table('order_items')->insert($orderItemData);

                    $orderTotal += $totalItemPrice;
                }
                
                // Cập nhật tổng giá đơn hàng
                DB::table('orders')->where('id', $orderId)->update(['total_price' => $orderTotal]);
            }
        }

        $this->command->info('Dữ liệu người mua hàng nhiều nhất đã được tạo thành công!');
    }
}
