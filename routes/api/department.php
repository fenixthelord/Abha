<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentsControllers;

Route::prefix('departments')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [DepartmentsControllers::class, 'index']);
        Route::get('/{uuid}/show', [DepartmentsControllers::class, 'show']);
        Route::post('/create', [DepartmentsControllers::class, 'store']);
        Route::put('/{uuid}/update', [DepartmentsControllers::class, 'update']);
        Route::delete('/{uuid}/destroy', [DepartmentsControllers::class, 'destroy']);
    });
});
