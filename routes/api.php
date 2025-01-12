<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\NotificationController;
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



Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
Route::post('/send-device-token', [NotificationController::class, 'saveDeviceToken']);

Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('upload', [UserController::class, 'addImage'])->middleware('auth:sanctum');
