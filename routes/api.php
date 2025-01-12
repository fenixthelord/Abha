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
    Route::post('/users/{userId}/permissions', [RoleAndPermissionController::class, 'assignPermission']);
    Route::post('/users/{userId}/roles', [RoleAndPermissionController::class, 'assignRole']);
    Route::post('/users/{userId}/remove', [RoleAndPermissionController::class, 'removeRoleFromUser']);
    Route::post('/users/{userId}/remove', [RoleAndPermissionController::class, ' RemoveDirectPermission']);
    Route::get('/users/{userId}/get', [RoleAndPermissionController::class, 'GetUserPermissions']);
    Route::post('/roles/remove',[RoleAndPermissionController::class,' RemovePermissionsFromRole']);
    Route::post('/roles/assign',[RoleAndPermissionController::class,' AssignPermissionsToRole']);







});
