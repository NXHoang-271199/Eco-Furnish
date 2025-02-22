<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PostsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CategoryPostController;



use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\VariantValueController;
use App\Http\Controllers\Admin\CategoryController;

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

});

Route::prefix('admin')->group(function () {
    Route::get('/', function(){
        return view('admins.dashboard');
    });

    // User ===============================================
    Route::resource('users', UsersController::class);

    // Post ===============================================
    Route::resource('posts', PostsController::class);

    // Category Post ======================================
    Route::resource('category-posts', CategoryPostController::class);

});


