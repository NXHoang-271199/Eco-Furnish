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
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'model' => null],
            ['name' => 'View Revenue', 'slug' => 'view-revenue', 'model' => null],
            ['name' => 'View User Stats', 'slug' => 'view-user-stats', 'model' => null],
            ['name' => 'View Product Stats', 'slug' => 'view-product-stats', 'model' => null],
            ['name' => 'View Order Stats', 'slug' => 'view-order-stats', 'model' => null],
            
            // User permissions
            ['name' => 'View Users', 'slug' => 'view-users', 'model' => 'App\Models\User'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'model' => 'App\Models\User'],
            ['name' => 'Update Users', 'slug' => 'update-users', 'model' => 'App\Models\User'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'model' => 'App\Models\User'],
            ['name' => 'Restore Users', 'slug' => 'restore-users', 'model' => 'App\Models\User'],
            
            // Role permissions
            ['name' => 'View Roles', 'slug' => 'view-roles', 'model' => 'App\Models\Role'],
            ['name' => 'Create Roles', 'slug' => 'create-roles', 'model' => 'App\Models\Role'],
            ['name' => 'Update Roles', 'slug' => 'update-roles', 'model' => 'App\Models\Role'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'model' => 'App\Models\Role'],
            
            // Post permissions
            ['name' => 'View Posts', 'slug' => 'view-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Create Posts', 'slug' => 'create-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Update Posts', 'slug' => 'update-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Delete Posts', 'slug' => 'delete-posts', 'model' => 'App\Models\Post'],
            ['name' => 'Restore Posts', 'slug' => 'restore-posts', 'model' => 'App\Models\Post'],
            
            // Category Post permissions
            ['name' => 'View Category Posts', 'slug' => 'view-category-posts', 'model' => 'App\Models\CategoryPost'],
            ['name' => 'Create Category Posts', 'slug' => 'create-category-posts', 'model' => 'App\Models\CategoryPost'],
            ['name' => 'Update Category Posts', 'slug' => 'update-category-posts', 'model' => 'App\Models\CategoryPost'],
            ['name' => 'Delete Category Posts', 'slug' => 'delete-category-posts', 'model' => 'App\Models\CategoryPost'],
            
            // Product permissions
            ['name' => 'View Products', 'slug' => 'view-products', 'model' => 'App\Models\Product'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'model' => 'App\Models\Product'],
            ['name' => 'Update Products', 'slug' => 'update-products', 'model' => 'App\Models\Product'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'model' => 'App\Models\Product'],
            ['name' => 'Restore Products', 'slug' => 'restore-products', 'model' => 'App\Models\Product'],
            
            // Category permissions
            ['name' => 'View Categories', 'slug' => 'view-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Create Categories', 'slug' => 'create-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Update Categories', 'slug' => 'update-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Delete Categories', 'slug' => 'delete-categories', 'model' => 'App\Models\Category'],
            ['name' => 'Restore Categories', 'slug' => 'restore-categories', 'model' => 'App\Models\Category'],
            
            // Variant permissions
            ['name' => 'View Variants', 'slug' => 'view-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Create Variants', 'slug' => 'create-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Update Variants', 'slug' => 'update-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Delete Variants', 'slug' => 'delete-variants', 'model' => 'App\Models\Variant'],
            ['name' => 'Restore Variants', 'slug' => 'restore-variants', 'model' => 'App\Models\Variant'],
            
            // Variant Value permissions
            ['name' => 'View Variant Values', 'slug' => 'view-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Create Variant Values', 'slug' => 'create-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Update Variant Values', 'slug' => 'update-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Delete Variant Values', 'slug' => 'delete-variant-values', 'model' => 'App\Models\VariantValue'],
            ['name' => 'Restore Variant Values', 'slug' => 'restore-variant-values', 'model' => 'App\Models\VariantValue'],
            
            // Comment permissions
            ['name' => 'View Comments', 'slug' => 'view-comments', 'model' => 'App\Models\Comment'],
            ['name' => 'Update Comments', 'slug' => 'update-comments', 'model' => 'App\Models\Comment'],
            ['name' => 'Delete Comments', 'slug' => 'delete-comments', 'model' => 'App\Models\Comment'],
            
            // Voucher permissions
            ['name' => 'View Vouchers', 'slug' => 'view-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Create Vouchers', 'slug' => 'create-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Update Vouchers', 'slug' => 'update-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Delete Vouchers', 'slug' => 'delete-vouchers', 'model' => 'App\Models\Voucher'],
            ['name' => 'Restore Vouchers', 'slug' => 'restore-vouchers', 'model' => 'App\Models\Voucher'],
            
            // Payment Method permissions
            ['name' => 'View Payment Methods', 'slug' => 'view-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            ['name' => 'Create Payment Methods', 'slug' => 'create-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            ['name' => 'Update Payment Methods', 'slug' => 'update-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            ['name' => 'Delete Payment Methods', 'slug' => 'delete-payment-methods', 'model' => 'App\Models\PaymentMethod'],
            
            // Order permissions
            ['name' => 'View Orders', 'slug' => 'view-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Create Orders', 'slug' => 'create-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Update Orders', 'slug' => 'update-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Delete Orders', 'slug' => 'delete-orders', 'model' => 'App\Models\Order'],
            ['name' => 'Restore Orders', 'slug' => 'restore-orders', 'model' => 'App\Models\Order'],
            
            // Order Notification permissions
            ['name' => 'View Order Notifications', 'slug' => 'view-order-notifications', 'model' => 'App\Models\OrderNotification'],
            ['name' => 'Update Order Notifications', 'slug' => 'update-order-notifications', 'model' => 'App\Models\OrderNotification'],
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
} 