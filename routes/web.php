<?php

use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Checkout\CartController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\OrderController;
use App\Http\Controllers\Payment\MpesaController;
use App\Http\Controllers\Payment\FlutterwaveController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/category/{category:slug}', [EventController::class, 'byCategory'])->name('events.category');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

/*
|--------------------------------------------------------------------------
| Checkout Routes
|--------------------------------------------------------------------------
*/
Route::prefix('checkout/{event:slug}')->name('checkout.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('select');
    Route::post('/reserve', [CartController::class, 'reserve'])->name('reserve');
    Route::get('/details', [CheckoutController::class, 'details'])->name('details');
    Route::post('/details', [CheckoutController::class, 'storeDetails'])->name('details.store');
    Route::get('/payment', [CheckoutController::class, 'payment'])->name('payment');
    Route::post('/payment/mpesa', [MpesaController::class, 'initiate'])->name('payment.mpesa');
    Route::post('/payment/card', [FlutterwaveController::class, 'initiate'])->name('payment.card');
    Route::get('/processing/{order}', [CheckoutController::class, 'processing'])->name('processing');
});

Route::prefix('orders')->name('orders.')->middleware('auth')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order:order_number}', [OrderController::class, 'show'])->name('show');
    Route::get('/{order:order_number}/confirmation', [OrderController::class, 'confirmation'])->name('confirmation');
    Route::get('/{order:order_number}/tickets/{ticket}/download', [OrderController::class, 'downloadTicket'])->name('ticket.download');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (excluded from CSRF, signed via gateway headers)
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks')->name('webhooks.')->middleware('throttle:60,1')->group(function () {
    Route::post('/mpesa/stk', [MpesaController::class, 'stkCallback'])->name('mpesa.stk');
    Route::post('/mpesa/c2b/validate', [MpesaController::class, 'c2bValidate'])->name('mpesa.c2b.validate');
    Route::post('/mpesa/c2b/confirm', [MpesaController::class, 'c2bConfirm'])->name('mpesa.c2b.confirm');
    Route::post('/flutterwave/callback', [FlutterwaveController::class, 'webhook'])->name('flutterwave');
});
