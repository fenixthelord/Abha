<?php
use App\Http\Controllers\Api\UploadFileController;
use App\Http\Controllers\Api\Event\EventController;
use Illuminate\Support\Facades\Route;


Route::post("upload/file" , [UploadFileController::class , "upload"]);

Route::group(["prefix" => "/event"], function () {
    Route::get('/', [EventController::class, 'list']);
//    Route::get('/show/{id}', [EventController::class, 'showEvent']);
    Route::post('/create', [EventController::class, 'createEvent']);
    Route::put('/update/{id}', [EventController::class, 'updateEvent']);
    Route::delete('/delete', [EventController::class, 'deleteEvent']);
});
