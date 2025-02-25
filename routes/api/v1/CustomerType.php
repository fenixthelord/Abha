<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Type\CustomerTypeController;

Route::prefix('typeCustomer')->group(function () {
    Route::get('/forms-with-fields', [CustomerTypeController::class, 'getFormsWithFields']);
    Route::get('/get-customers', [CustomerTypeController::class, 'getCustomersByType']);
    Route::get('/get-form-submission-values', [CustomerTypeController::class, 'getFormSubmissionValues']);
    Route::post('/update-form-submission-status', [CustomerTypeController::class, 'updateStatus']);
    Route::delete('/delete-customer-type', [CustomerTypeController::class, 'deleteCustomersByType']);
});
