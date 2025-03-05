<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
 use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\PostApiController;

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

// User routes
Route::prefix('users')->group(function () {
    Route::get('/', [UserApiController::class, 'index']);
    Route::post('/', [UserApiController::class, 'store']);
    Route::get('/{id}', [UserApiController::class, 'show']);
    Route::put('/{id}', [UserApiController::class, 'update']);
});

// Post routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostApiController::class, 'index']);
    Route::get('/{id}', [PostApiController::class, 'show']);
});
