<?php

use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Excel\ExcelReportController;


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('excel')->group(function () {
        Route::get('export-services', [ExcelReportController::class, 'exportServicesToExcel']);
        Route::get('/export-audit-logs', [ExcelReportController::class, 'exportAuditLogsToExcel']);
    });
});
