<?php

use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use Illuminate\Support\Facades\Route;

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

// Authentication Routes
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
// Login Throw Social
Route::post('social-login', [SocialLoginController::class, 'login']);

Route::prefix('/auth')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('upload', [UserController::class, 'addImage'])->middleware('auth:sanctum');

});
