<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentsControllers;

Route::prefix('departments')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/', [DepartmentsControllers::class, 'index']);
            Route::get('/show', [DepartmentsControllers::class, 'show']);
            Route::post('/create', [DepartmentsControllers::class, 'store']);
            Route::match(['put','patch'],'update', [DepartmentsControllers::class, 'update']);
            Route::delete('/destroy', [DepartmentsControllers::class, 'destroy']);
        });
    });
});
