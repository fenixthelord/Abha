<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController;


Route::group(["prefix" => "org", "middleware" => ["auth:sanctum", "activeVerify"]], function () {
    Route::get('/list', [OrganizationController::class, "index"]);
    Route::get("/list/filter", [OrganizationController::class, "filter"]);
    Route::delete('/delete', [OrganizationController::class, "delete"]);
    Route::post('/department/employee', [OrganizationController::class, 'getDepartmentEmployees']);
    Route::post('/employee/add', [OrganizationController::class, 'AddEmployee']);
    Route::post('/employee/update', [OrganizationController::class, 'UpdateEmployee']);
    Route::post('/manager/employee', [OrganizationController::class, 'getDepartmentManagers']);
});
