<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Tạo vai trò
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $staffRole = Role::create([
            'name' => 'Staff',
            'slug' => 'staff',
        ]);

        $clientRole = Role::create([
            'name' => 'Client',
            'slug' => 'client',
        ]);

        // Tạo các quyền
        $permissions = [
            // Dashboard permissions
            ['name' => 'Xem Tổng Quan', 'slug' => 'view-dashboard', 'model' => null],
            ['name' => 'Xem Doanh Thu', 'slug' => 'view-revenue', 'model' => null],
            ['name' => 'Xem Thống Kê Người Dùng', 'slug' => 'view-user-stats', 'model' => null],
            ['name' => 'Xem Thống Kê Sản Phẩm', 'slug' => 'view-product-stats', 'model' => null],
            ['name' => 'Xem Thống Kê Đơn Hàng', 'slug' => 'view-order-stats', 'model' => null],
            
            // User permissions
            ['name' => 'Xem Người Dùng', 'slug' => 'view-users', 'model' => 'App\Models\User'],
            ['name' => 'Thêm Người Dùng', 'slug' => 'create-users', 'model' => 'App\Models\User'],
            ['name' => 'Cập Nhật Người Dùng', 'slug' => 'update-users', 'model' => 'App\Models\User'],
            ['name' => 'Xóa Người Dùng', 'slug' => 'delete-users', 'model' => 'App\Models\User'],
            ['name' => 'Khôi Phục Người Dùng', 'slug' => 'restore-users', 'model' => 'App\Models\User'],
            
            // Role permissions
            ['name' => 'Xem Vai Trò', 'slug' => 'view-roles', 'model' => 'App\Models\Role'],
            ['name' => 'Thêm Vai Trò', 'slug' => 'create-roles', 'model' => 'App\Models\Role'],
            ['name' => 'Cập Nhật Vai Trò', 'slug' => 'update-roles', 'model' => 'App\Models\Role'],
            ['name' => 'Xóa Vai Trò', 'slug' => 'delete-roles', 'model' => 'App\Models\Role'],
            
            // Post permissions
            ['name' => 'Xem Bài Viết', 'slug' => 'view-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Thêm Bài Viết', 'slug' => 'create-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Cập Nhật Bài Viết', 'slug' => 'update-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Xóa Bài Viết', 'slug' => 'delete-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Khôi Phục Bài Viết', 'slug' => 'restore-posts', 'model' => 'App\Models\Post'],
            
            // Category Post permissions
            ['name' => 'Xem Danh Mục Bài Viết', 'slug' => 'view-category-posts', 'model' => 'App\Models\CategoryPost'],
            ['name' => 'Thêm Danh Mục Bài Viết', 'slug' => 'create-category-posts', 'model' => 'App\Models\CategoryPost'],
            ['name' => 'Cập Nhật Danh Mục Bài Viết', 'slug' => 'update-category-posts', 'model' => 'App\Models\CategoryPost'],
            ['name' => 'Xóa Danh Mục Bài Viết', 'slug' => 'delete-category-posts', 'model' => 'App\Models\CategoryPost'],
            
            // Product permissions
            ['name' => 'Xem Sản Phẩm', 'slug' => 'view-products', 'model' => 'App\Models\Product'],
            ['name' => 'Thêm Sản Phẩm', 'slug' => 'create-products', 'model' => 'App\Models\Product'],
            ['name' => 'Cập Nhật Sản Phẩm', 'slug' => 'update-products', 'model' => 'App\Models\Product'],
            ['name' => 'Xóa Sản Phẩm', 'slug' => 'delete-products', 'model' => 'App\Models\Product'],
            ['name' => 'Khôi Phục Sản Phẩm', 'slug' => 'restore-products', 'model' => 'App\Models\Product'],
            
            // Category permissions
            ['name' => 'Xem Danh Mục', 'slug' => 'view-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Thêm Danh Mục', 'slug' => 'create-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Cập Nhật Danh Mục', 'slug' => 'update-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Xóa Danh Mục', 'slug' => 'delete-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Khôi Phục Danh Mục', 'slug' => 'restore-categories', 'model' => 'App\Models\Category'],
            
            // Variant permissions
            ['name' => 'Xem Biến Thể', 'slug' => 'view-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Thêm Biến Thể', 'slug' => 'create-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Cập Nhật Biến Thể', 'slug' => 'update-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Xóa Biến Thể', 'slug' => 'delete-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Khôi Phục Biến Thể', 'slug' => 'restore-variants', 'model' => 'App\Models\Variant'],
            
            // Variant Value permissions
            ['name' => 'Xem Giá Trị Biến Thể', 'slug' => 'view-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Thêm Giá Trị Biến Thể', 'slug' => 'create-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Cập Nhật Giá Trị Biến Thể', 'slug' => 'update-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Xóa Giá Trị Biến Thể', 'slug' => 'delete-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Khôi Phục Giá Trị Biến Thể', 'slug' => 'restore-variant-values', 'model' => 'App\Models\VariantValue'],
            
            // Comment permissions
            ['name' => 'Xem Bình Luận', 'slug' => 'view-comments', 'model' => 'App\Models\Comment'],
            ['name' => 'Cập Nhật Bình Luận', 'slug' => 'update-comments', 'model' => 'App\Models\Comment'],
            ['name' => 'Xóa Bình Luận', 'slug' => 'delete-comments', 'model' => 'App\Models\Comment'],
            
            // Voucher permissions
            ['name' => 'Xem Mã Giảm Giá', 'slug' => 'view-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Thêm Mã Giảm Giá', 'slug' => 'create-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Cập Nhật Mã Giảm Giá', 'slug' => 'update-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Xóa Mã Giảm Giá', 'slug' => 'delete-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Khôi Phục Mã Giảm Giá', 'slug' => 'restore-vouchers', 'model' => 'App\Models\Voucher'],
            
            // Payment Method permissions
            ['name' => 'Xem Phương Thức Thanh Toán', 'slug' => 'view-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            ['name' => 'Thêm Phương Thức Thanh Toán', 'slug' => 'create-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            ['name' => 'Cập Nhật Phương Thức Thanh Toán', 'slug' => 'update-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            ['name' => 'Xóa Phương Thức Thanh Toán', 'slug' => 'delete-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            
            // Order permissions
            ['name' => 'Xem Đơn Hàng', 'slug' => 'view-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Tạo Đơn Hàng', 'slug' => 'create-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Cập Nhật Đơn Hàng', 'slug' => 'update-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Xóa Đơn Hàng', 'slug' => 'delete-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Khôi Phục Đơn Hàng', 'slug' => 'restore-orders', 'model' => 'App\Models\Order'],
            
            // Order Notification permissions
            ['name' => 'Xem Thông Báo Đơn Hàng', 'slug' => 'view-order-notifications', 'model' => 'App\Models\OrderNotification'],
            ['name' => 'Cập Nhật Thông Báo Đơn Hàng', 'slug' => 'update-order-notifications', 'model' => 'App\Models\OrderNotification'],
        ];

        // Tạo các quyền trong database
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Gán tất cả quyền cho Admin
        $adminRole->givePermissionTo(Permission::all());

        // Gán quyền cho Staff
        $staffPermissions = Permission::whereIn('slug', [
            // Dashboard
            'view-dashboard', 'view-product-stats', 'view-order-stats',
            
            // Posts
            'view-posts', 'create-posts', 'update-posts',
            'view-category-posts', 'create-category-posts', 'update-category-posts',
            
            // Products
            'view-products', 'create-products', 'update-products',
            'view-categories', 'create-categories', 'update-categories',
            'view-variants', 'create-variants', 'update-variants',
            'view-variant-values', 'create-variant-values', 'update-variant-values',
            
            // Comments
            'view-comments', 'update-comments',
            
            // Vouchers
            'view-vouchers', 'create-vouchers', 'update-vouchers',
            
            // Orders
            'view-orders', 'update-orders',
            'view-payment-methods', 'update-payment-methods',
            'view-order-notifications', 'update-order-notifications',
        ])->get();
        
        $staffRole->givePermissionTo($staffPermissions);

        // Gán quyền cho Client
        $clientPermissions = Permission::whereIn('slug', [
            'view-posts',
            'view-products',
            'view-vouchers',
            'create-orders',
            'view-orders',
        ])->get();
        
        $clientRole->givePermissionTo($clientPermissions);
    }

    private function getModelName($slug)
    {
        $names = [
            'posts' => 'bài viết',
            'products' => 'sản phẩm'
        ];
        return $names[$slug] ?? $slug;
    }

    private function getModelClass($slug)
    {
        $classes = [
            'posts' => 'App\Models\Post',
            'products' => 'App\Models\Product'
        ];
        return $classes[$slug] ?? null;
    }
} 