<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:20,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt');
});

Route::middleware('jwt')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::put('me', [AuthController::class, 'updateMe']);

    // Lecture : admin et autorité nationale (supervision)
    Route::get('users', [UserController::class, 'index'])->middleware('role:ADMIN,NATIONAL_AUTHORITY');

    // Écriture : admin uniquement
    Route::middleware('role:ADMIN')->group(function () {
        Route::patch('users/{id}/role', [UserController::class, 'updateRole'])->whereUuid('id');
        Route::patch('users/{id}/status', [UserController::class, 'updateStatus'])->whereUuid('id');
    });
});
