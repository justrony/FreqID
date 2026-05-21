<?php

use App\Http\Controllers\FaceScanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/face-scan', [FaceScanController::class, 'receiveScan']);
Route::post('/face-register', [FaceScanController::class, 'registerFace'])->middleware('auth:sanctum');

