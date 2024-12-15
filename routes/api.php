<?php

use App\Http\Controllers\PornstarApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */

// show all pornstars as a json
Route::get('/pornstars', [PornstarApiController::class, 'index']);
// )->middleware('auth:sanctum');

// show one pornstar
Route::get('/pornstars/{id}', [PornstarApiController::class, 'show']);
// )->middleware('auth:sanctum');

Route::get('/pornstars/{id}/thumbnails/{thumb_id}', [PornstarApiController::class, 'getThumbnail']);

// prevent get requests on this post route
Route::get('/pornstars/{pornstar}/refreshCache', function(Request $request) {
    abort(400);
});

// force a manual refresh of cache
Route::post('/pornstars/{pornstar}/refreshCache', [PornstarApiController::class, 'refreshCache']);
// )->middleware('auth:sanctum');

// prevent users from posting to the get api
Route::post('/pornstars', function(Request $request) {
    abort(400);
});

// prevent users from posting to the get api
Route::post('/pornstars/{id}', function(Request $request, string $id) {
    abort(400);
});