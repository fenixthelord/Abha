<?php

use App\Http\Controllers\Api\Forms\FormTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Forms\FormBuilderController;
use App\Http\Controllers\Api\Forms\FormFieldController;
use App\Http\Controllers\Api\Forms\FormSubmissionController;

Route::group(["prefix" => "/forms"], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/', [FormBuilderController::class, 'list'])->name('forms.list');
            Route::post('/', [FormBuilderController::class, 'store'])->name('forms.store');
            Route::get('/show', [FormBuilderController::class, 'show'])->name('forms.show');
            Route::put('/{form}', [FormBuilderController::class, 'update'])->name('forms.update');
            Route::delete('/{form}', [FormBuilderController::class, 'destroy'])->name('forms.destroy');
            Route::put('/update', [FormBuilderController::class, 'update'])->name('forms.update');
            Route::delete('delete', [FormBuilderController::class, 'destroy'])->name('forms.destroy');
            Route::post('/{form_id}/fields', [FormFieldController::class, 'store']);
            Route::delete('/fields/{id}', [FormFieldController::class, 'destroy']);
            Route::get('/{form_id}/submissions', [FormSubmissionController::class, 'showFormWithSubmissions']);
            Route::post('/{form_id}/submit', [FormSubmissionController::class, 'store']);
        });
    });
});

Route::group(["prefix" => "form-types"], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/', [FormTypeController::class, 'index'])->name('form-types.index');
            Route::get('/{id}', [FormTypeController::class, 'show'])->name('form-types.show');
            Route::post('/', [FormTypeController::class, 'store'])->name('form-types.store');
            Route::put('/{id}', [FormTypeController::class, 'update'])->name('form-types.update');
            Route::delete('/{id}', [FormTypeController::class, 'destroy'])->name('form-types.destroy');
        });
    });
});
