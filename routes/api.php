<?php

use App\Http\Controllers\PornstarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/pornstars', [PornstarController::class, 'index']);
// )->middleware('auth:sanctum');

Route::get('/pornstars/{id}', [PornstarController::class, 'show']);
// )->middleware('auth:sanctum');

Route::post('/pornstars/refresh', [PornstarController::class, 'refresh']);
// )->middleware('auth:sanctum');