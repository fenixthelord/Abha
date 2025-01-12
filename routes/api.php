<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();


});

    Route::post('logout',[UserController::class,'logout'])->middleware('auth:sanctum');
Route::post('register',[UserController::class,'register']);
Route::post('login',[UserController::class,'login']);
Route::post('upload',[UserController::class,'addImage'])->middleware('auth:sanctum');
Route::post('send',[UserController::class,'sendOTP'])->middleware('auth:sanctum');
Route::post('/auth/forgot-password', [ChangePassword::class, 'forgotPassword']);
Route::post('/auth/reset-password', [ChangePassword::class, 'reset_password']);

