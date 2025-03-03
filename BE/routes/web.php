<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderNotificationController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\VariantValueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TrashController;

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


// Admin Routes
Route::prefix('admin')->group(function () {
    // Categories routes
    Route::resource('categories', CategoryController::class);

    // Products routes
    Route::resource('products', ProductController::class);
    Route::post('products/generate-variants', [ProductController::class, 'generateVariants'])->name('products.generate-variants');

    // Variants routes
    Route::resource('variants', VariantController::class);
    
    // Variant Values Routes
    Route::prefix('variants/{variant}')->name('variants.')->group(function () {
        Route::resource('values', VariantValueController::class);
    });

    // Routes cho thùng rác
    Route::prefix('trash')->group(function () {
        Route::resource('trash-products', TrashController::class)->only(['index', 'update', 'destroy']);
        Route::resource('trash-categories', TrashController::class)->only(['index', 'update', 'destroy']);
        Route::resource('trash-variants', TrashController::class)->only(['index', 'update', 'destroy']);
        Route::resource('trash-variant-values', TrashController::class)->only(['index', 'update', 'destroy']);
        Route::get('restore-variant/{id}', [VariantController::class, 'restore']);
        Route::get('restore-variant-value/{id}', [VariantValueController::class, 'restore']);
        Route::get('restore-category/{id}', [CategoryController::class, 'restore']);
    });
Route::get('/', function () {
    return view('admins.dashboard');
    // return view('admins.test');
});
Route::prefix('admin')->group(function () {

    // phương thức thanh toán
    Route::resource('payment-methods', PaymentMethodController::class);
    /// kết nối phương thức
    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('{id}/connect', [PaymentMethodController::class, 'getConnectForm'])->name('connect.form');
        Route::post('{id}/connect', [PaymentMethodController::class, 'connect'])->name('connect');
        Route::post('{id}/disconnect', [PaymentMethodController::class, 'disconnect'])->name('disconnect');
    });

    // đơn hàng
    Route::resource('orders', OrderController::class);
    // cập nhật trạng thái đơn hàng
    Route::post('orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('order.updateStatus');

    // thông báo đơn hàng
    // thông báo đơn hàng
    Route::get('order-notifications', [OrderNotificationController::class, 'index'])->name('order.notifications');
    Route::post('order-notifications/{id}/read', [OrderNotificationController::class, 'markAsRead'])->name('order.notification.read');
});
