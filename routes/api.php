<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/;
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('login', [UserAuthController::class, 'login']);
Route::Post('logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/upload', [UserAuthController::class, 'addImage'])
    ->middleware('auth:sanctum');
Route::get('token', function () {
    return ('Welcome to Abha');
})->middleware('auth:sanctum');

Route::post('/send-message', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
Route::Get('/receive-message', [\App\Http\Controllers\ChatController::class, 'receiveMessage']);


