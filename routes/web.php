<?php

use App\Http\Controllers\PornstarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PornstarController::class, 'index']);
