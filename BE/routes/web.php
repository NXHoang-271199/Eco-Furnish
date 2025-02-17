<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PostsController;


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

Route::prefix('admin')->group(function () {
    Route::get('/', function(){
        return view('admins.dashboard');
    });

    // User ===============================================
    Route::resource('users', UsersController::class);


    // Post ===============================================
    Route::resource('posts', PostsController::class);

});


