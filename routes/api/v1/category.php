<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;

Route::group(["prefix" => "categories", "middleware" => ["auth:sanctum", "activeVerify"]], function () {
    Route::get("/show", [CategoryController::class, "list"]);
    Route::get("/filter", [CategoryController::class, "filter"]);
    Route::post("/department/create", [CategoryController::class, "create"]);
    Route::match(['put','patch'],"/department/update", [CategoryController::class, "update"]);
    Route::delete("/delete", [CategoryController::class, "delete"]);
});
