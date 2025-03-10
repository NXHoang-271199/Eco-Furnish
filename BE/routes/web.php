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
        Route::resource('categories', CategoryController::class);

        // Products routes
        Route::resource('products', ProductController::class);
        Route::post('products/generate-variants', [ProductController::class, 'generateVariants'])->name('products.generate-variants');
        // Variants routes
        Route::resource('variants', VariantController::class);
        Route::prefix('variants/{variant}')->name('variants.')->group(function () {
            Route::resource('values', VariantValueController::class);
        });

        // Users Management
        Route::resource('users', UserController::class);
        Route::put('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        // Roles Management
        Route::resource('roles', RoleController::class);

        // Posts Management
        Route::resource('posts', PostController::class);
        Route::resource('category-posts', CategoryPostController::class);
        Route::post('upload-image', [ImageUploadController::class, 'upload'])->name('upload.image');

        // Vouchers Management
        Route::resource('vouchers', VoucherController::class);

        // Payment Methods
        Route::resource('payment-methods', PaymentMethodController::class);
        Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
            Route::get('{id}/connect', [PaymentMethodController::class, 'getConnectForm'])->name('connect.form');
            Route::post('{id}/connect', [PaymentMethodController::class, 'connect'])->name('connect');
            Route::post('{id}/disconnect', [PaymentMethodController::class, 'disconnect'])->name('disconnect');
        });

        // Orders Management
        Route::resource('orders', OrderController::class);
        Route::post('orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('order.updateStatus');

        // Order Notifications
        Route::get('order-notifications', [OrderNotificationController::class, 'index'])->name('order.notifications');
        Route::post('order-notifications/{id}/read', [OrderNotificationController::class, 'markAsRead'])->name('order.notification.read');

        // Trash Management
        Route::prefix('trash')->group(function () {
            Route::resource('trash-products', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::resource('trash-categories', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::resource('trash-variants', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::resource('trash-variant-values', TrashController::class)->only(['index', 'update', 'destroy']);
            Route::get('restore-variant/{id}', [VariantController::class, 'restore']);
            Route::get('restore-variant-value/{id}', [VariantValueController::class, 'restore']);
            Route::get('restore-category/{id}', [CategoryController::class, 'restore']);
        });
    });
});
