<?php

use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentsControllers;

Route::prefix('services')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/index', [ServiceController::class, 'index']); //done
            Route::get('/show/{id}', [ServiceController::class, 'show']); //done
            Route::post('/add', [ServiceController::class, 'store']); //done
            Route::put('/update/{id}', [ServiceController::class, 'update']); //done
            Route::delete('/delete/{id}', [ServiceController::class, 'destroy']); //done
        });
    });
});
