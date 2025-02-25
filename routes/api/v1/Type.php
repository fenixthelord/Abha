<?php

use App\Http\Controllers\Api\Excel\ExcelReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Type\TypeController;

Route::prefix('types')->group(function () {
    Route::get('/index', [TypeController::class, 'index']);
    Route::post('/store', [TypeController::class, 'store']);
    Route::get('/show', [TypeController::class, 'show']);
    Route::match(['put', 'patch'], '/update', [TypeController::class, 'update']);
    Route::get('/get-service', [TypeController::class, 'getServiceByType']);
    Route::get("/get-type-by-form" , [TypeController::class , "getTypeByForm"]);

    Route::get('/export/types', [ExcelReportController::class, 'exportTypes']);
});
