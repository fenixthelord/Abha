<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Auth\SocialLoginController;


Route::prefix('auth')->group(function () {
    Route::middleware('activeVerify')->group(function () {
        Route::post('register', [UserAuthController::class, 'register'])->middleware('auth:sanctum');
        Route::post('active', [UserController::class, 'active']);
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('get-verify', [UserController::class, 'sendOtp']);
            Route::post('cheek-verify', [UserController::class, 'verifyOtp']);
        });
    });

        Route::post('login', [UserAuthController::class, 'login']);
        Route::post('/forgot-password', [ChangePasswordController::class, 'forgotPassword']);
        Route::post('/reset-password', [ChangePasswordController::class, 'resetPassword']);
        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::post('refresh-token', [UserAuthController::class, 'refreshToken']);
        Route::post('/link-social', [SocialLoginController::class, 'linkSocialAccount'])
            ->name('auth.link-social');
});
