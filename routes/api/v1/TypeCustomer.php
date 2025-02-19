<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Type\TypeCustomerController;

Route::get('/forms-with-fields', [TypeCustomerController::class, 'getFormsWithFields']);
