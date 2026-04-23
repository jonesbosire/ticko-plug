<?php

use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\PaymentStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Check-In API (used by scanner app / browser scanner)
|--------------------------------------------------------------------------
*/
Route::prefix('checkin')->middleware('throttle:120,1')->group(function () {
    Route::post('/scan', [CheckInController::class, 'scan'])->name('api.checkin.scan');
    Route::post('/session/start', [CheckInController::class, 'startSession'])->middleware('auth:sanctum')->name('api.checkin.session.start');
    Route::post('/session/end', [CheckInController::class, 'endSession'])->middleware('auth:sanctum')->name('api.checkin.session.end');
});

/*
|--------------------------------------------------------------------------
| Ticket API
|--------------------------------------------------------------------------
*/
Route::prefix('tickets')->middleware('auth:sanctum')->group(function () {
    Route::get('/{ticketNumber}', [TicketController::class, 'show'])->name('api.tickets.show');
    Route::post('/{ticketNumber}/whatsapp', [TicketController::class, 'sendToWhatsApp'])->name('api.tickets.whatsapp');
});

/*
|--------------------------------------------------------------------------
| Payment Status Polling (for STK push waiting screen)
|--------------------------------------------------------------------------
*/
Route::get('/payment/status/{order}', [PaymentStatusController::class, 'status'])
    ->middleware('throttle:30,1')
    ->name('api.payment.status');

// Order status polling (used by checkout/processing.blade.php)
Route::get('/orders/{orderNumber}/status', [PaymentStatusController::class, 'status'])
    ->middleware('throttle:30,1')
    ->name('api.orders.status');
