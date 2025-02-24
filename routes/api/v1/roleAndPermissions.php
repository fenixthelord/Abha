<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleAndPermissionController;

Route::prefix('roles-and-permissions')->middleware('auth:sanctum')->group(function () {

    Route::middleware('activeVerify')->group(function () {
        Route::get('/', [RoleAndPermissionController::class, 'index']);
        Route::post('/create', [RoleAndPermissionController::class, 'store']);
        Route::put('/{id}', [RoleAndPermissionController::class, 'update']);
        Route::delete('/{id}', [RoleAndPermissionController::class, 'destroy']);
        Route::prefix('roles')->group(function () {
            Route::post('/remove', [RoleAndPermissionController::class, 'RemovePermissionsFromRole']);
            Route::post('/assign', [RoleAndPermissionController::class, 'AssignPermissionsToRole']);
            Route::post('/delete', [RoleAndPermissionController::class, 'DeleteRole']);
        });
        Route::prefix('permissions')->group(function () {
            // temporary solution
            Route::get('clear-permission-cache', [RoleAndPermissionController::class, 'clearPermissionCache']);
            Route::get('/get', [RoleAndPermissionController::class, 'GetAllPermissions']);
        });
        Route::post('/permission/create', [RoleAndPermissionController::class, 'CreatePermission']);
        Route::post('role/sync', [RoleAndPermissionController::class, 'SyncPermission']);
        Route::prefix('users')->group(function () {
            Route::post('/roles', [RoleAndPermissionController::class, 'assignRole']);
            Route::post('/permissions', [RoleAndPermissionController::class, 'assignPermission']);
            Route::post('/remove', [RoleAndPermissionController::class, 'removeRoleFromUser']);
            Route::post('/direct/remove', [RoleAndPermissionController::class, 'RemoveDirectPermission']);
            Route::post('/get', [RoleAndPermissionController::class, 'GetUserPermissions']);
        });
    });
});

