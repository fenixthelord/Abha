<?php

use App\Http\Controllers\Api\Workflows\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "/"], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::apiResource('workflows', WorkflowController::class);
        });
    });
});
