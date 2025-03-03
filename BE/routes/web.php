<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryPostController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Providers\RouteServiceProvider;use App\Http\Controllers\ProductController;
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
    // return view('admins.dashboard');
    return view('auth.login');
});

// FRONT-END
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
 
    return redirect(RouteServiceProvider::ADMIN);
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
 
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

// Route login không cần middleware auth
Route::prefix('admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login');
    Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');
});

// Các route khác cần middleware auth
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    // Dashboard =========================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User ===============================================
    Route::resource('users', UserController::class);

    // Role ===============================================
    Route::resource('roles', RoleController::class);

    // Post ===============================================
    Route::resource('posts', PostController::class);

    // Category Post ======================================
    Route::resource('category-posts', CategoryPostController::class);
    Route::post('upload-image', [App\Http\Controllers\ImageUploadController::class, 'upload'])->name('upload.image');

    // Voucher ============================================
    Route::resource('vouchers', VoucherController::class);
});
