<?php

use App\Http\Controllers\PornstarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */

// show all pornstars as a json
Route::get('/pornstars', [PornstarController::class, 'index']);
// )->middleware('auth:sanctum');

// show one pornstar
Route::get('/pornstars/{id}', [PornstarController::class, 'show']);
// )->middleware('auth:sanctum');

// prevent get requests on this post route
Route::get('/pornstars/refreshData', function(Request $request) {
    abort(400);
});

// prevent get requests on this post route
Route::get('/pornstars/refreshCache', function(Request $request) {
    abort(400);
});

// prevent users from posting to the get api
Route::post('/pornstars', function(Request $request) {
    abort(400);
});

// prevent users from posting to the get api
Route::post('/pornstars/{id}', function(Request $request, string $id) {
    abort(400);
});

// force a manual refresh of data and recaching images
Route::post('/pornstars/refreshData', [PornstarController::class, 'refreshData']
)->middleware(['throttle:']);

// force a manual refresh of cache
Route::post('/pornstars/refreshCache', [PornstarController::class, 'refreshCache']);
// )->middleware('auth:sanctum');