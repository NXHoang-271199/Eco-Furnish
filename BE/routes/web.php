<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryPostController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\VariantValueController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TrashController;

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

// Admin Routes
Route::prefix('admin')->group(function () {
    // Categories routes
    Route::resource('categories', CategoryController::class);

    // Products routes
    Route::resource('products', ProductController::class);

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
    // return view('admins.dashboard');
    // return view('admins.test');
});
// Login
Route::get('login', [LoginController::class, 'showFormLogin'])->name('login');
Route::get('register', [RegisterController::class, 'showFormRegister'])->name('register');

Route::prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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


