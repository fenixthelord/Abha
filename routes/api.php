<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleAndPermissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('roles-and-permissions')->group(function (){
    Route::get('/', [RoleAndPermissionController::class,'index']);
    Route::post('/create',[RoleAndPermissionController::class,'store']);
    Route::post('/permission/create',[RoleAndPermissionController::class,'CreatePermission']);
    Route::put('/{id}',[RoleAndPermissionController::class,'update']);
    Route::delete('/{id}',[RoleAndPermissionController::class,'destroy']);
    Route::post('/roles/remove',[RoleAndPermissionController::class,'RemovePermissionsFromRole']);
    Route::post('/roles/assign',[RoleAndPermissionController::class,'AssignPermissionsToRole']);
    Route::post('/role/sync',[RoleAndPermissionController::class,'SyncPermission']);
    Route::prefix('users')->group(function () {
        Route::post('/permissions', [RoleAndPermissionController::class, 'assignPermission']);
        Route::post('/roles', [RoleAndPermissionController::class, 'assignRole']);
        Route::post('/remove', [RoleAndPermissionController::class, 'removeRoleFromUser']);
        Route::post('/direct/remove', [RoleAndPermissionController::class, 'RemoveDirectPermission']);
        Route::get('/{userId}/get', [RoleAndPermissionController::class, 'GetUserPermissions']);});








});
