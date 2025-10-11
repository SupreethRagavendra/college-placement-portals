<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registration routes
    Route::get('register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('register', [AuthController::class, 'register']);

    // Login routes
    Route::get('login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('login', [AuthController::class, 'login']);

    // Password reset routes (removed as not implemented in AuthController)
    // Route::get('forgot-password', [AuthController::class, 'showResetPassword'])
    //     ->name('password.request');

    // Route::post('forgot-password', [AuthController::class, 'resetPassword'])
    //     ->name('password.email');
});

Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');
});
