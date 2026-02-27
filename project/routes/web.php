<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/home');
});

Route::middleware('auth')->get('/home', function () {
    return view('home');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login','showLoginForm')->name('login');
    Route::post('/login','login')->name('login.post');
    Route::post('/logout','logout')->name('logout');
});

Route::middleware('auth')->controller(UserController::class)
    ->prefix('/admin')->name('user.')->group(function () {

    Route::get('/register','showUserForm')->name('create');
    Route::post('/store','store')->name('store');
});




