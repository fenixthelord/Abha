<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\ExcelController;
use App\Http\Controllers\Api\LanguageController;

Route::group(["prefix" => "/db"], function () {
    Route::get('/tables', [DatabaseController::class, 'getTables']);
    Route::get('/columns/{table}', [DatabaseController::class, 'getColumns']);
});
Route::post('/extract-column', [ExcelController::class, 'extractColumn']);

Route::get('lang/{locale}', [LanguageController::class, 'swap'])->middleware("changeLang");
