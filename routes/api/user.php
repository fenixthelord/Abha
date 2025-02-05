<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::prefix('user')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/all', [UserController::class, 'index']);
            Route::get('/me', [UserController::class, 'userProfile']);
            Route::post('upload', [UserController::class, 'addImage']);
            Route::post('/update-profile', [UserController::class, 'updateProfile']);
            Route::post('update', [UserController::class, 'updateAdmin']);
            Route::post('delete-user', [UserController::class, 'deleteUser']);
            Route::get('show-deleted', [UserController::class, 'showDeleteUser']);
            Route::post('restore_user', [UserController::class, 'restoreUser']);
            Route::post('search', [UserController::class, 'searchUser']);
        });
    });
});
