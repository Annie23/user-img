<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);

Route::middleware('token.auth')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});

Route::get('/token', [UserController::class, 'getToken']);
