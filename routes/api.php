<?php

use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\api\auth\ChangePassword;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Change Lang
Route::get('lang/{locale}', [LanguageController::class, 'swap'])->middleware("changeLang");

// Send Notifications
Route::post('/send-notification', [NotificationController::class, 'sendNotification']);


// Login Throw Social (***** For Customers Only ******) Don't Use it
Route::post('/auth/social-login', [SocialLoginController::class, 'login'])
    ->name('auth.social-login');


Route::prefix('/auth')->group(function () {
    // Authentication Routes
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {

        // Link Social Account Route (Requires Authentication)
        Route::post('/auth/link-social', [SocialLoginController::class, 'linkSocialAccount'])
            ->middleware('auth:sanctum')
            ->name('auth.link-social');
        Route::post('upload', [UserController::class, 'addImage']);
        Route::post('logout', [UserController::class, 'logout']);
    });
});
Route::post('send',[UserController::class,'sendOTP'])->middleware('auth:sanctum');
Route::post('/auth/forgot-password', [ChangePassword::class, 'forgotPassword']);
Route::post('/auth/reset-password', [ChangePassword::class, 'reset_password']);

