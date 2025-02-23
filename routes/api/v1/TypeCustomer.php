<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Type\TypeCustomerController;

Route::prefix('typeCustomer')->group(function () {
    Route::get('/forms-with-fields', [TypeCustomerController::class, 'getFormsWithFields']);
    Route::get('/get-customers', [TypeCustomerController::class, 'getCustomersByType']);
    Route::get('/get-form-submission-values', [TypeCustomerController::class, 'getFormSubmissionValues']);
    Route::post('/update-form-submission-status', [TypeCustomerController::class, 'updateStatus']);
    Route::delete('/delete-customer-type', [TypeCustomerController::class, 'deleteCustomersByType']);
});
