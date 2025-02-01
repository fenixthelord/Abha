<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DepartmentsControllers;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoleAndPermissionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotifyGroupController;


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

// Change Lang
Route::get('lang/{locale}', [LanguageController::class, 'swap'])->middleware("changeLang");



// Login Throw Social (***** For Customers Only ******) Don't Use it
Route::post('/auth/social-login', [SocialLoginController::class, 'login'])
    ->name('auth.social-login');


Route::prefix('/auth')->group(function () {
    // Authentication Routes
    Route::post('register', [UserAuthController::class, 'register'])->middleware('auth:sanctum');
    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('/forgot-password', [ChangePasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [ChangePasswordController::class, 'reset_password']);
    Route::post('refresh-token', [UserAuthController::class, 'refreshToken']);
    Route::middleware('auth:sanctum')->group(function () {
        // Link Social Account Route (Requires Authentication)
        Route::post('/auth/link-social', [SocialLoginController::class, 'linkSocialAccount'])
            ->name('auth.link-social');
        Route::post('active', [UserController::class, 'active']);
        Route::post('logout', [UserAuthController::class, 'logout']);
    });
});


Route::prefix('/user')->group(function () {
    route::middleware('auth:sanctum')->group(function () {
        //   Route::middleware('activeVerify')->group(function () {
        Route::get('/all', [UserController::class, 'index']);
        Route::post('/me', [UserController::class, 'user_profile']);
        Route::get('/me', [UserController::class, 'user_profile']);
        Route::post('send', [UserController::class, 'sendOTP']);
        Route::post('update-profile', [UserController::class, 'update']);
        Route::post('update', [UserController::class, 'updateAdmin']);
        Route::post('upload', [UserController::class, 'addImage']);
        Route::post('delete-user', [UserController::class, 'deleteUser']);
        Route::get('show-deleted', [UserController::class, 'showDeleteUser']);
        Route::post('restore_user', [UserController::class, 'restoreUser']);
        Route::post('search', [UserController::class, 'searchUser']);
    });
    //   });
});
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('activeVerify')->group(function () {
        Route::get('get-verify', [UserController::class, 'sendOtp']);
        Route::post('cheek-verify', [UserController::class, 'verifyOtp']);
    });
});

// Role And Permission
//require_once __DIR__ . '/Api/roles-and-permissions/roles-and-permissions.php';


Route::prefix('roles-and-permissions')->middleware('auth:sanctum')->group(function () {
    //   Route::middleware('activeVerify')->group(function () {
    Route::get('/', [RoleAndPermissionController::class, 'index']);
    Route::post('/create', [RoleAndPermissionController::class, 'store']);
    Route::post('/permission/create', [RoleAndPermissionController::class, 'CreatePermission']);
    Route::put('/{id}', [RoleAndPermissionController::class, 'update']);
    Route::delete('/{id}', [RoleAndPermissionController::class, 'destroy']);
    Route::post('/roles/remove', [RoleAndPermissionController::class, 'RemovePermissionsFromRole']);
    Route::post('/roles/assign', [RoleAndPermissionController::class, 'AssignPermissionsToRole']);
    Route::post('/role/sync', [RoleAndPermissionController::class, 'SyncPermission']);
    Route::post('roles/delete', [RoleAndPermissionController::class, 'DeleteRole']);
    Route::get('permissions/get', [RoleAndPermissionController::class, 'GetAllPermissions']);
    Route::prefix('users')->group(function () {
        Route::post('/permissions', [RoleAndPermissionController::class, 'assignPermission']);
        Route::post('/roles', [RoleAndPermissionController::class, 'assignRole']);
        Route::post('/remove', [RoleAndPermissionController::class, 'removeRoleFromUser']);
        Route::post('/direct/remove', [RoleAndPermissionController::class, 'RemoveDirectPermission']);
        Route::post('/get', [RoleAndPermissionController::class, 'GetUserPermissions']);
    });
    //   });
});

Route::get('/audit-logs', [AuditLogController::class, 'index']);



// *************** Notification *******************

Route::prefix('notification')->group(function () {

    Route::post('/send', [NotificationController::class, 'sendNotification']);
});
// Send Notifications
//Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
Route::post('/save-device-token', [NotificationController::class, 'saveDeviceToken']);



Route::prefix('notify-groups')->group(function () {
    Route::get('/', [NotifyGroupController::class, 'allGroup']);

    Route::get('/{groupUuid}/show', [NotifyGroupController::class, 'groupDetail']);
    Route::post('/{groupUuid}/edit', [NotifyGroupController::class, 'editGroup']);

    Route::post('/create', [NotifyGroupController::class, 'createNotifyGroup']);

    Route::post('/{notifyGroupId}/users', [NotifyGroupController::class, 'addUsersToNotifyGroup']);

    Route::delete('/{notifyGroupId}/users', [NotifyGroupController::class, 'removeUsersFromNotifyGroup']);

    Route::post('/{notifyGroupId}/send-notification', [NotifyGroupController::class, 'sendNotificationToNotifyGroup']);
    Route::delete('/{notifyGroupId}/delete', [NotifyGroupController::class, 'deleteNotifyGroup']);
});

Route::post('/notifications', [NotificationController::class, 'store'])
    ->middleware('auth:sanctum');

Route::get('/user/notifications', [NotificationController::class, 'getUserNotifications'])
    ->middleware('auth:sanctum');


/**
 * All Departments and Categories
 *
 */

Route::group(["prefix" => "/categories"], function () {

    Route::get("/show", [CategoryController::class, "list"]);
    Route::get("/filter", [CategoryController::class, "filter"]);
    Route::post("/department/create", [CategoryController::class, "create"]);
    Route::put("/department/update", [CategoryController::class, "update"]);
    Route::delete("/delete", [CategoryController::class, "delete"]);
});
Route::prefix('departments')->group(function () {
    Route::get('/', [DepartmentsControllers::class, 'index']);
    Route::get('/{uuid}/show', [DepartmentsControllers::class, 'show']);
    Route::post('/create', [DepartmentsControllers::class, 'store']);
    Route::put('/{uuid}/update', [DepartmentsControllers::class, 'update']);
    Route::delete('/{uuid}/destroy', [DepartmentsControllers::class, 'destroy']);
});
