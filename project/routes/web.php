<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolClassController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('usuarios');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/home/turmas', function () {
        return view('components.quick-access.qa-turmas');
    })->name('turmas');

    Route::get('/home/escolas', function () {
        return view('components.quick-access.qa-escolas');
    })->middleware('can:access-admin')->name('escolas');

    Route::get('/home/usuarios', function () {
        return view('pages.admin.qa-usuario');
    })->middleware('can:access-admin')->name('usuarios');

});

Route::middleware(['auth','can:access-admin'])->prefix('/usuarios')
    ->controller(UserController::class)->name('usuario.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}/update', 'update')->name('update');
        Route::patch('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/delete', 'destroy')->name('destroy');
    });

    Route::middleware(['auth','can:access-admin'])->prefix('/escolas')
    ->controller(SchoolController::class)->name('escola.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{school}/edit', 'edit')->name('edit');
        Route::put('/{school}/update', 'update')->name('update');
        Route::delete('/{school}/delete', 'destroy')->name('destroy');
    });

    Route::middleware(['auth'])->prefix('/turmas')
    ->controller(SchoolClassController::class)->name('turma.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{schoolClass}/edit', 'edit')->name('edit');
        Route::put('/{schoolClass}/update', 'update')->name('update');
        Route::delete('/{schoolClass}/delete', 'destroy')->name('destroy');
    });

Route::middleware('auth')->prefix('/home')
    ->controller(RelatorioController::class)->name('relatorio.')->group(function () {
        Route::get('/','grafico')->name('inicio');
        Route::get('/html','gerarHTML')->name('html');
        Route::get('/xls','gerarXLS')->name('xls');
    });


