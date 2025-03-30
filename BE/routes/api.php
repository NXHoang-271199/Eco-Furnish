<?php

use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\VoucherApiController;

use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\CategoryPostApiController;
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

Route::post('/send-message', function (Request $request) {
    $message = $request->input('message');
    event(new MessageSent($message));
    return response()->json(['status' => 'Message sent']);
});

Route::prefix('messages')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [MessageController::class, 'store']); // Lưu tin nhắn
        Route::get('/user/{userId}', [MessageController::class, 'getUserMessages']); // Lấy tin nhắn theo user
        Route::get('/unread', [MessageController::class, 'getUnreadMessages']); // Lấy tin chưa đọc
        Route::patch('/read/{messageId}', [MessageController::class, 'markAsRead']); // Đánh dấu đã đọc
        Route::patch('/read-all/{userId}', [MessageController::class, 'markAllAsRead']); // Đánh dấu tất cả là đã đọc
        Route::post('/admin/send', [MessageController::class, 'sendByAdmin']); // Admin gửi tin nhắn
    });
});

// Route xác thực token cho socket server
Route::middleware('auth:sanctum')->post('/auth/verify-token', function (Request $request) {
    $user = $request->user();

    // Chuẩn hóa role
    // Kiểm tra xem request có chứa header Origin không (để biết nó đến từ đâu)
    $origin = $request->header('Origin');

    // Nếu request đến từ trang admin, luôn trả về role là admin
    if (strpos($origin, 'admin') !== false) {
        $role = 'admin';
    } else {
        // Xử lý thông thường cho các trường hợp khác
        $role = is_string($user->role) ? $user->role : 'user';
        if (is_object($user->role)) {
            $role = $user->role->name === 'admin' ? 'admin' : 'user';
        }
    }

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'avatar' => $user->avatar
        ],
        'verified' => true
    ]);
});

// Route đơn giản để kiểm tra token
Route::get('/auth/check-token', function (Request $request) {
    return response()->json(['message' => 'Bạn có quyền truy cập API này', 'token_valid' => true]);
})->middleware('auth:sanctum');

// Route test kiểm tra xác thực
Route::get('/test-auth', function (Request $request) {
    $user = Auth::user();
    if ($user) {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_id' => $user->role_id ?? null
            ],
            'token' => $request->bearerToken()
        ]);
    }
    return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
})->middleware('auth:sanctum');

// API đơn giản để lấy thông tin người dùng từ ID (cho socket server)
Route::get('/users/{id}', function ($id) {
    $user = \App\Models\User::find($id);
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role_id' => $user->role_id
    ]);
});
Route::post('/momo/ipn', [PaymentMethodController::class, 'handleMoMoIPN']); // không được động // FE ko được động tới
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

