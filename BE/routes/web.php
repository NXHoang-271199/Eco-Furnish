<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryPostController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\VoucherController;

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
    // return view('admins.dashboard');
    return view('admins.test');
});
// Login
Route::get('login', [LoginController::class, 'showFormLogin'])->name('login');
Route::get('register', [RegisterController::class, 'showFormRegister'])->name('register');

Route::prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User ===============================================
    Route::resource('users', UserController::class);

    // Post ===============================================
    Route::resource('posts', PostController::class);
    Route::post('/posts/{id}/approve', [PostController::class, 'approve'])->name('posts.approve');

    // Category Post ======================================
    Route::resource('category-posts', CategoryPostController::class);
    Route::post('upload-image', [App\Http\Controllers\ImageUploadController::class, 'upload'])->name('upload.image');

    // Voucher ============================================
    Route::resource('vouchers', VoucherController::class);

});


