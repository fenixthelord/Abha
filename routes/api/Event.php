<?php
use \Illuminate\Routing\Route;
use App\Http\Controllers\Api\UploadFileController;
use App\Http\Controllers\Api\Event\EventController;


Route::post("upload/file" , [UploadFileController::class , "upload"]);

Route::group(["prefix" => "/event"], function () {
    Route::get('/', [EventController::class, 'list']);
//    Route::get('/show/{id}', [EventController::class, 'showEvent']);
    Route::post('/create', [EventController::class, 'createEvent']);
    Route::put('/update/{id}', [EventController::class, 'updateEvent']);
    Route::delete('/delete/{id}', [EventController::class, 'createEvent']);
});
