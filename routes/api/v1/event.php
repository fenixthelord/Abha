<?php
use App\Http\Controllers\Api\UploadFileController;
use App\Http\Controllers\Api\Event\EventController;
use Illuminate\Support\Facades\Route;


Route::post("upload/file" , [UploadFileController::class , "upload"]);

Route::group(["prefix" => "/event"], function () {
    Route::get('/', [EventController::class, 'list']);
    Route::post('/create', [EventController::class, 'createEvent']);
    Route::put('/update', [EventController::class, 'updateEvent']);
    Route::delete('/delete', [EventController::class, 'deleteEvent']);
});
