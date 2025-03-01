<?php

use Illuminate\Support\Facades\Route;
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
});
