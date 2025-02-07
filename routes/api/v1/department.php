<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentsControllers;

Route::prefix('departments')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/', [DepartmentsControllers::class, 'index']);
            Route::get('/{id}/show', [DepartmentsControllers::class, 'show']);
            Route::post('/create', [DepartmentsControllers::class, 'store']);
            Route::put('/{id}/update', [DepartmentsControllers::class, 'update']);
            Route::delete('/{id}/destroy', [DepartmentsControllers::class, 'destroy']);
        });
    });
});
