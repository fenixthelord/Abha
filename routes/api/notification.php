<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotifyGroupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('notification')->group(function () {
        Route::post('/send', [NotificationController::class, 'sendNotification']);
        Route::post('/all', [NotificationController::class, 'allNotification']);
    });
    Route::post('/notifications', [NotificationController::class, 'store']);

    Route::get('/user/notifications', [NotificationController::class, 'getUserNotifications']);

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
});
