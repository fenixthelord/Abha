<?php


use App\Http\Controllers\Api\Ticket\TicketCommentController;
use App\Http\Controllers\Api\Ticket\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/all', [TicketController::class, 'index']);
        Route::post('/create', [TicketController::class, 'store']);
        Route::put('/update/{id}', [TicketController::class, 'update']);

        Route::post('/comments', [TicketCommentController::class, 'store']);
        Route::put('/comments/{id}', [TicketCommentController::class, 'update']);
    });
});
