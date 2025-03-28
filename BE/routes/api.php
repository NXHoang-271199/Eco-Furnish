<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\VoucherApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\CategoryPostApiController;
use App\Http\Controllers\Api\DiscountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Category routes
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);

// Chat routes
Route::post('/chat', [ChatController::class, 'chat']);

// User routes
Route::prefix('users')->group(function () {
    Route::get('/', [UserApiController::class, 'index']);
    Route::get('/{id}', [UserApiController::class, 'show']);
    Route::post('/register', [UserApiController::class, 'register']);
    Route::post('/login', [UserApiController::class, 'login']);
    Route::post('/forgot-password', [UserApiController::class, 'forgotPassword']);
    Route::post('/reset-password', [UserApiController::class, 'resetPassword']);
    Route::post('/refresh-token', [UserApiController::class, 'refreshToken']);
    Route::post('/verify-email', [UserApiController::class, 'verifyEmail']);
    Route::post('/resend-verification', [UserApiController::class, 'resendVerification']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/{id}/profile', [UserApiController::class, 'updateProfile']);
        Route::post('/logout', [UserApiController::class, 'apiLogout']);
    });
});

// Post routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostApiController::class, 'index']);
    Route::get('/{slug}', [PostApiController::class, 'show']);
    Route::get('/category/{categorySlug}', [PostApiController::class, 'getByCategory']);
});

// Category Post routes
Route::prefix('category-posts')->group(function () {
    Route::get('/', [CategoryPostApiController::class, 'index']);
    Route::get('/{slug}', [CategoryPostApiController::class, 'show']);
});

// Voucher routes
Route::prefix('vouchers')->group(function () {
    Route::get('/', [VoucherApiController::class, 'index']);
    Route::get('/{code}', [VoucherApiController::class, 'show']);
    Route::post('/check-voucher', [VoucherApiController::class, 'checkVoucher']);
});

// Comment routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
});

Route::get('/products/{product}/comments', [CommentController::class, 'getProductComments']);

// Banner routes
Route::get('/banners', [BannerController::class, 'index']);

// Cart Routers
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']); // Lấy giỏ hàng
    Route::post('/cart/add', [CartController::class, 'addToCart']); // Thêm vào giỏ hàng
    Route::put('/cart/update/{id}', [CartController::class, 'updateQuantity']); // Cập nhật số lượng
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']); // Xóa 1 sản phẩm
    Route::delete('/cart/clear', [CartController::class, 'clearCart']); // Xóa toàn bộ giỏ hàng
});

// Payment Method routes
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::post('/momo/ipn', [PaymentMethodController::class, 'handleMoMoIPN']); // FE ko được động tới
Route::get('/vnpay/ipn', [PaymentMethodController::class, 'handleVNPAYIPN']); // FE ko được động tới

// Order routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']); // Danh sách đơn hàng
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Chi tiết đơn hàng
    Route::post('/orders', [OrderController::class, 'createOrder']); // Tạo đơn hàng
    Route::post('/orders/buy-now', [OrderController::class, 'quickOrder']); // Tạo đơn hàng nhanh
    Route::post('/orders/{id}/refund', [OrderController::class, 'refundOrder']); // Hoàn hàng
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']); // Hủy đơn
});


// Discount routes
Route::post('/discounts/verify', [DiscountController::class, 'verify']);
