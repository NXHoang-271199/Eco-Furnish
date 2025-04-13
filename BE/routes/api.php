<?php

use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\VariantApiController;
use App\Http\Controllers\Api\VoucherApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\CategoryPostApiController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\UserNotificationController;

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
    $user = $request->user();
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'phone' => $user->phone,
        'email' => $user->email,
        'role_id' => $user->role_id,
        'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
    ]);
});

// Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Thêm route để lấy sản phẩm bán chạy
Route::get('/best-sellers', [ProductController::class, 'getBestSellers']);

// Category routes
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/all', [CategoryApiController::class, 'all']);
Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);

// Variant routes
Route::get('/variants', [VariantApiController::class, 'index']);

// Chat routes
Route::post('/chat', [ChatController::class, 'chat']);
Route::get('/chat/welcome', [ChatController::class, 'sendWelcomeMessage']);
Route::post('/chat/order-success', [ChatController::class, 'sendOrderSuccessMessage']);

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
    Route::put('/update/{id}', [UserApiController::class, 'updateProfile']);

    Route::middleware('auth:sanctum')->group(function () {
        // Thêm routes cho quản lý địa chỉ
        Route::get('/{userId}/addresses', [UserAddressController::class, 'getUserAddresses']);
        Route::post('/{userId}/addresses', [UserAddressController::class, 'storeUserAddress']);
        Route::put('/{userId}/addresses/{addressId}', [UserAddressController::class, 'updateUserAddress']);
        Route::delete('/{userId}/addresses/{addressId}', [UserAddressController::class, 'deleteUserAddress']);

        Route::post('/upload-avatar/{id}', [UserApiController::class, 'uploadAvatar']);
        Route::put('/{id}/profile', [UserApiController::class, 'updateProfile']);
        Route::post('/logout', [UserApiController::class, 'apiLogout']);
    });
});

// Thêm Social OAuth routes
Route::prefix('auth')->middleware('web')->group(function () {
    Route::get('/google/redirect', [UserApiController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [UserApiController::class, 'handleGoogleCallback']);
    Route::get('/facebook/redirect', [UserApiController::class, 'redirectToFacebook']);
    Route::get('/facebook/callback', [UserApiController::class, 'handleFacebookCallback']);
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
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/check-voucher', [VoucherApiController::class, 'checkVoucher']); // checkvoucher
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

        // *** Thêm Route: Admin đánh dấu tin nhắn của client là đã đọc ***
        Route::patch('/mark-client-messages-as-read/{clientId}', [MessageController::class, 'markClientMessagesAsReadByAdmin'])
            ->middleware('auth:sanctum'); // Đảm bảo chỉ admin mới gọi được (cần kiểm tra role trong controller)
        // *** Kết thúc thêm ***
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
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $role,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
        ],
        'verified' => true
    ]);
});

// Route đơn giản để kiểm tra token
Route::get('/auth/check-token', function (Request $request) {
    return response()->json(['message' => 'Bạn có quyền truy cập API này', 'token_valid' => true]);
})->middleware('auth:sanctum');

// Route test kiểm tra xác thực
Route::middleware('auth:sanctum')->get('/auth/check', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'message' => 'Bạn đã đăng nhập thành công',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
        ]
    ]);
});

// API đơn giản để lấy thông tin người dùng từ ID (cho socket server)
Route::get('/users/{id}', function ($id) {
    $user = \App\Models\User::find($id);
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'phone' => $user->phone,
        'email' => $user->email,
        'role_id' => $user->role_id,
        'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
    ]);
});

// API cho thông báo người dùng
Route::middleware('auth:sanctum')->prefix('user/notifications')->group(function () {
    Route::get('/', [UserNotificationController::class, 'index']);
    Route::patch('/{id}/read', [UserNotificationController::class, 'markAsRead']);
    Route::patch('/read-all', [UserNotificationController::class, 'markAllAsRead']);
});

// Route::post('/momo/ipn', [PaymentMethodController::class, 'handleMoMoIPN']); // không được động // FE ko được động tới
// Cart Routers
Route::middleware('auth:sanctum')->group(function () {
    Route::get('cart', [CartController::class, 'index']); // Lấy giỏ hàng
    Route::post('cart/add', [CartController::class, 'addToCart']); // Thêm vào giỏ hàng
    Route::put('cart/update/{id}', [CartController::class, 'updateQuantity']); // Cập nhật số lượng
    Route::delete('cart/remove/{id}', [CartController::class, 'removeFromCart']); // Xóa 1 sản phẩm
    Route::delete('cart/clear', [CartController::class, 'clearCart']); // Xóa toàn bộ giỏ hàng
});

// Payment Method routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('payment-methods', [PaymentMethodController::class, 'index']); // đổ danh sách thanh toán
    Route::get('payment-methods/deposit', [PaymentMethodController::class, 'getDepositMethods']); // đổ danh sách thanh toán cho nạp tiền vào ví
    Route::post('payment-method/retry/{orderId}', [PaymentMethodController::class, 'retryPayment']); // api gọi lại trang thanh toán cho đơn hàng
    Route::post('payment-method/retry-deposit/{id}', [PaymentMethodController::class, 'retryDepositPayment']); // api nạp tiền lại
});
Route::post('/momo/ipn', [PaymentMethodController::class, 'handleMoMoIPN']); // FE ko được động tới
Route::get('/vnpay/ipn', [PaymentMethodController::class, 'handleVNPAYIPN']); // FE ko được động tới

// Order routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('orders', [OrderController::class, 'index']); // Danh sách đơn hàng
    Route::get('orders/{id}', [OrderController::class, 'show']); // Chi tiết đơn hàng
    Route::post('orders', [OrderController::class, 'createOrder']); // Tạo đơn hàng
    Route::post('orders/buy-now', [OrderController::class, 'quickOrder']); // Tạo đơn hàng nhanh
    Route::post('orders/{id}/request-refund', [OrderController::class, 'requestRefund']); // Gửi yêu cầu hoàn hàng
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancelOrder']); // Hủy đơn
    Route::post('orders/{id}/confirm', [OrderController::class, 'confirmOrder']); //Xác nhận đã nhận hàng
    Route::post('check-voucher', [VoucherApiController::class, 'checkVoucher']); // checkvoucher
});

// Thêm route cho lấy đơn hàng chưa thanh toán
Route::middleware('auth:sanctum')->get('user/orders/unpaid', [OrderController::class, 'getUnpaidOrders']); // Lấy danh sách đơn hàng chưa thanh toán trực tuyến

// review routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']); // tạo đánh giá sản phẩm
    Route::get('/products/{productId}/can-review', [ReviewController::class, 'canReview']); // kiểm tra quyền đánh giá
    Route::get('/orders/{orderId}/reviews', [ReviewController::class, 'getOrderReviews']); // lấy đánh giá của một đơn hàng
});
Route::get('products/{productId}/reviews', [ReviewController::class, 'getProductReviews']); // đổ danh sách đánh giá sản phẩm

Route::middleware('auth:sanctum')->group(function () {
    Route::get('wallet/balance', [WalletController::class, 'getBalance']); // số dư ví
    Route::post('wallet/deposit', [WalletController::class, 'deposit']); // nạp tiền
    Route::get('wallet/transactions', [WalletController::class, 'transactions']); // lịch sử giao dịch
    Route::delete('/wallet/transactions/{id}/cancel', [WalletController::class, 'cancelTransaction']); // hủy giao dịch
});
