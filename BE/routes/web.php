<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderNotificationController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;

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
