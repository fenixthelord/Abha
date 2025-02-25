<?php

use App\Http\Controllers\Api\Position\PositionController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "position", "middleware" => ["auth:sanctum", "activeVerify"]], function () {
    Route::get("/", [PositionController::class, "index"]);
    // TODO new chart to users 
    Route::get("chart" , [PositionController::class, "chart"]);
    Route::post("/create", [PositionController::class, "create"]);
    Route::match(['put', 'patch', 'post'], '/update', [PositionController::class, 'update']);
    Route::delete('/delete', [PositionController::class, 'delete']);    
    Route::match(['put', 'patch', 'post'], '/update/user', [PositionController::class, 'updateUserPosition']);
    Route::match(['put', 'patch', 'post'] , '/user/delete', [PositionController::class, 'deleteUser']);
});
