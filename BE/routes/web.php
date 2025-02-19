<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryPostController;
use App\Http\Controllers\Auth\RegisterController;



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
    Route::get('/', [DashboardController::class, 'index'])->name('admins.dashboard');

    // User ===============================================
    Route::resource('users', UsersController::class);

    // Post ===============================================
    Route::resource('posts', PostsController::class);

    // Category Post ======================================
    Route::resource('category-posts', CategoryPostController::class);
    Route::post('upload-image', [App\Http\Controllers\ImageUploadController::class, 'upload'])->name('upload.image');

});


