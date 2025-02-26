<?php

use App\Http\Controllers\Api\Event\EventController;
use App\Http\Controllers\Api\UploadFileController;
use Illuminate\Support\Facades\Route;


Route::post("upload/file", [UploadFileController::class, "upload"]);

Route::group(["prefix" => "/event"], function () {
    Route::get('/', [EventController::class, 'list']);
    Route::get('/show', [EventController::class, 'showEvent']);
    Route::post('/show/form', [EventController::class, 'showEventForm']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::post('/create', [EventController::class, 'createEvent']);
            Route::put('/update', [EventController::class, 'updateEvent']);
            Route::delete('/delete', [EventController::class, 'deleteEvent']);
        });
    });
});
