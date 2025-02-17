<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Type\TypeController;

Route::prefix('types')->group(function () {
    Route::get('/index', [TypeController::class, 'index']);
    Route::post('/store', [TypeController::class, 'store']);
    Route::get('/show', [TypeController::class, 'show']);
    Route::match(['put', 'patch'], '/update', [TypeController::class, 'update']);
    Route::post('/get-service', [TypeController::class, 'getServiceByType']);
});
