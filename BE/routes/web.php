<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\CategoryPostController;
use App\Http\Controllers\VariantValueController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\OrderNotificationController;
use App\Http\Controllers\Admin\AdminResetPasswordController;
use App\Http\Controllers\Admin\AdminForgotPasswordController;
use App\Http\Controllers\Admin\PermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {

    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

        // Forgot Password Routes
        Route::get('/forgot-password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('admin.password.request');
        Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('admin.password.email');
        Route::get('/reset-password/{token}', [AdminResetPasswordController::class, 'showResetForm'])
            ->name('admin.password.reset');
        Route::post('/reset-password', [AdminResetPasswordController::class, 'reset'])
            ->name('admin.password.update');
    });

    // Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {

        // Logout route
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Categories Management
        Route::middleware(['permission:view-categories'])->group(function () {
            Route::resource('categories', CategoryController::class);
        });

        // Products routes
        Route::middleware(['permission:view-products'])->group(function () {
            Route::resource('products', ProductController::class);
            Route::post('products/generate-variants', [ProductController::class, 'generateVariants'])->name('products.generate-variants');
        });
        
        // Variants routes
        Route::middleware(['permission:view-variants'])->group(function () {
            Route::resource('variants', VariantController::class);
            Route::prefix('variants/{variant}')->name('variants.')->group(function () {
                Route::resource('values', VariantValueController::class);
            });
        });

        // Users Management
        Route::middleware(['permission:view-users'])->group(function () {
            Route::resource('users', UserController::class);
            Route::put('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])
                ->name('users.toggle-status');
        });

        // Roles Management
        Route::middleware(['permission:view-roles'])->group(function () {
            Route::resource('roles', RoleController::class);
        });

        // Posts Management
        Route::middleware(['permission:view-posts'])->group(function () {
            Route::resource('posts', PostController::class);
        });
        
        // Category Posts Management
        Route::middleware(['permission:view-category-posts'])->group(function () {
            Route::resource('category-posts', CategoryPostController::class);
            Route::post('upload-image', [ImageUploadController::class, 'upload'])->name('upload.image');
        });

        // Vouchers Management
        Route::middleware(['permission:view-vouchers'])->group(function () {
            Route::resource('vouchers', VoucherController::class);
        });

        // Payment Methods
        Route::middleware(['permission:view-payment-methods'])->group(function () {
            Route::resource('payment-methods', PaymentMethodController::class);
            Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
                Route::get('{id}/connect', [PaymentMethodController::class, 'getConnectForm'])->name('connect.form');
                Route::post('{id}/connect', [PaymentMethodController::class, 'connect'])->name('connect');
                Route::post('{id}/disconnect', [PaymentMethodController::class, 'disconnect'])->name('disconnect');
            });
        });

        // Orders Management
        Route::middleware(['permission:view-orders'])->group(function () {
            Route::resource('orders', OrderController::class);
            Route::post('orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('order.updateStatus');
        });
        
        // Order Notifications
        Route::middleware(['permission:view-order-notifications'])->group(function () {
            Route::get('order-notifications', [OrderNotificationController::class, 'index'])->name('order.notifications');
            Route::post('order-notifications/{id}/read', [OrderNotificationController::class, 'markAsRead'])->name('order.notification.read');
        });

        // Comments Management
        Route::middleware(['permission:view-comments'])->group(function () {
            Route::resource('comments', CommentController::class)->only(['index', 'show', 'destroy']);
            Route::post('comments/{comment}/toggle-status', [CommentController::class, 'toggleStatus'])->name('comments.toggle-status');
            Route::get('products/{product}/comments', [CommentController::class, 'productComments'])->name('comments.product');
            Route::get('users/{user}/info', [CommentController::class, 'userInfo'])->name('comments.user-info');
        });

        // Trash Management
        Route::middleware(['permission:view-dashboard'])->prefix('trash')->group(function () {
            Route::resource('trash-products', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::resource('trash-categories', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::resource('trash-variants', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::resource('trash-variant-values', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::get('restore-variant/{id}', [VariantController::class, 'restore']);
            Route::get('restore-variant-value/{id}', [VariantValueController::class, 'restore']);
            Route::get('restore-category/{id}', [CategoryController::class, 'restore']);
        });
    });

    // Thêm routes cho quản lý quyền
    Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
        // Quản lý quyền
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        
        // Quản lý vai trò
        Route::get('/permissions/roles/{role}', [PermissionController::class, 'showRole'])->name('permissions.role');
        Route::put('/permissions/roles/{role}', [PermissionController::class, 'updateRolePermissions'])->name('permissions.update-role-permissions');
        Route::get('/permissions/roles/create', [PermissionController::class, 'createRole'])->name('permissions.create-role');
        Route::post('/permissions/roles', [PermissionController::class, 'storeRole'])->name('permissions.store-role');
        Route::delete('/permissions/roles/{role}', [PermissionController::class, 'destroyRole'])->name('permissions.destroy-role');
        
        // Quản lý quyền
        Route::get('/permissions/create', [PermissionController::class, 'createPermission'])->name('permissions.create-permission');
        Route::post('/permissions', [PermissionController::class, 'storePermission'])->name('permissions.store-permission');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroyPermission'])->name('permissions.destroy-permission');
    });
});
