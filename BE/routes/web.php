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

    // Variants routes
    Route::resource('variants', VariantController::class);
    
    // Variant Values Routes
    Route::prefix('variants/{variant}')->name('variants.')->group(function () {
        Route::resource('values', VariantValueController::class);
    });

    // Routes cho thùng rác
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/products', [TrashController::class, 'products'])->name('products');
        Route::post('/products/{id}/restore', [TrashController::class, 'restoreProduct'])->name('products.restore');
        Route::post('/products/{id}/force-delete', [TrashController::class, 'forceDeleteProduct'])->name('products.force-delete');
        
        Route::get('/categories', [TrashController::class, 'categories'])->name('categories');
        Route::post('/categories/{id}/restore', [TrashController::class, 'restoreCategory'])->name('categories.restore');
        Route::post('/categories/{id}/force-delete', [TrashController::class, 'forceDeleteCategory'])->name('categories.force-delete');
        
        Route::get('/variants', [TrashController::class, 'variants'])->name('variants');
        Route::post('/variants/{id}/restore', [TrashController::class, 'restoreVariant'])->name('variants.restore');
        Route::post('/variants/{id}/force-delete', [TrashController::class, 'forceDeleteVariant'])->name('variants.force-delete');
        
        Route::get('/variant-values', [TrashController::class, 'variantValues'])->name('variant-values');
        Route::post('/variant-values/{id}/restore', [TrashController::class, 'restoreVariantValue'])->name('variant-values.restore');
        Route::post('/variant-values/{id}/force-delete', [TrashController::class, 'forceDeleteVariantValue'])->name('variant-values.force-delete');
    });
});
