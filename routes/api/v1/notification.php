<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceToken\DeviceTokenController;
use App\Http\Controllers\Api\Notification\NotificationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('activeVerify')->group(function () {

        // Route::prefix('notification')->group(function () {
        //     Route::post('/send', [NotificationController::class, 'sendNotification']);
        // });
        // Route::post('notifications/all', [NotificationController::class, 'allNotification']);
        // Route::post('/notifications', [NotificationController::class, 'store']);

        // Route::get('/user/notifications', [NotificationController::class, 'getUserNotifications']);

        Route::post('/save-device-token', [DeviceTokenController::class, 'saveDeviceToken']);
        Route::get('/received-notifications', [DeviceTokenController::class, 'getReceivedNotifications']);
        Route::get('/sent-notifications/', [DeviceTokenController::class, 'getSentNotifications']);
        Route::prefix('notify-groups')->group(function () {
//            Route::get('/', [NotifyGroupController::class, 'allGroup']);
//            Route::get('/{groupId}/show', [NotifyGroupController::class, 'groupDetail']);
//            Route::post('/{groupId}/edit', [NotifyGroupController::class, 'editGroup']);
//            Route::post('/create', [NotifyGroupController::class, 'createNotifyGroup']);
//            Route::post('/{notifyGroupId}/users', [NotifyGroupController::class, 'addUsersToNotifyGroup']);
//            Route::delete('/{notifyGroupId}/users', [NotifyGroupController::class, 'removeUsersFromNotifyGroup']);
            Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
//            Route::delete('/{notifyGroupId}/delete', [NotifyGroupController::class, 'deleteNotifyGroup']);
        });
    });
});
