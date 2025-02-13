<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController;

/**
 * The database has been modified :
 * When adding the first organization containing a manager and an employee
 * A field (row) will be created for the manager and the value will be Null
 * 
 *   ... |   manger_id       |   employee_id    | ...
 *       |                   |                  |
 *       |     NUll          |  uuid_manager_1  |
 *       |  uuid_manager_1   |   employee_id    |
 */


Route::group(["prefix" => "org", "middleware" => ["auth:sanctum", "activeVerify"]], function () {
    Route::get('/list', [OrganizationController::class, "index"]);
    Route::get('/list/chart', [OrganizationController::class, "chart"]);
    Route::get("/list/filter", [OrganizationController::class, "filter"]);
    Route::delete('/delete', [OrganizationController::class, "delete"]);
    Route::post('/department/employee', [OrganizationController::class, 'getDepartmentEmployees']);
    Route::post('/employee/add', [OrganizationController::class, 'AddEmployee']);
    Route::post('/employee/update', [OrganizationController::class, 'UpdateEmployee']);
    Route::post('/manager/employee', [OrganizationController::class, 'getDepartmentManagers']);
});
