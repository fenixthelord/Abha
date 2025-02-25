<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::prefix('user')->group(function () {
            route::post('show', [UserController::class, 'show']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/all', [UserController::class, 'index']);
            Route::get('/me', [UserController::class, 'userProfile']);
            Route::post('upload', [UserController::class, 'addImage']);
            Route::match(['put','patch'],'/update-profile', [UserController::class, 'update']);
            Route::match(['put','patch'],'update', [UserController::class, 'updateAdmin']);
            Route::delete('delete-user', [UserController::class, 'deleteUser']);
            Route::get('show-deleted', [UserController::class, 'showDeleteUser']);
            Route::post('restore_user', [UserController::class, 'restoreUser']);
            Route::post('search', [UserController::class, 'searchUser']);
            Route::post('change-email', [UserController::class, 'emailOtp']);
            Route::put('change-email', [UserController::class, 'changeEmail']);
        });
    });
});
