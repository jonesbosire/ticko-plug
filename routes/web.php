<?php

use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Checkout\CartController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\OrderController;
use App\Http\Controllers\Payment\MpesaController;
use App\Http\Controllers\Payment\FlutterwaveController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Organizer\CheckInScannerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SEO: Sitemap + Robots
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-static.xml', [SitemapController::class, 'static'])->name('sitemap.static');
Route::get('/sitemap-events.xml', [SitemapController::class, 'events'])->name('sitemap.events');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories'])->name('sitemap.categories');
Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Disallow: /manage/',
        'Disallow: /checkout/',
        'Disallow: /orders/',
        'Disallow: /scan/',
        '',
        'Sitemap: ' . route('sitemap.index'),
    ]);
    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

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
// Manual auth (email + password)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Google OAuth
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

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

// Requires auth — personal ticket list
Route::prefix('orders')->name('orders.')->middleware('auth')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
});

// No auth required — guests can view their order after checkout
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/{order:order_number}', [OrderController::class, 'show'])->name('show');
    Route::get('/{order:order_number}/confirmation', [OrderController::class, 'confirmation'])->name('confirmation');
    Route::get('/{order:order_number}/tickets/{ticket}/download', [OrderController::class, 'downloadTicket'])->name('ticket.download');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (excluded from CSRF, signed via gateway headers)
|--------------------------------------------------------------------------
*/
// M-Pesa webhooks — IP-whitelisted to Safaricom ranges
Route::prefix('webhooks/mpesa')->name('webhooks.mpesa.')
    ->middleware(['throttle:60,1', 'mpesa.whitelist'])
    ->group(function () {
        Route::post('/stk', [MpesaController::class, 'stkCallback'])->name('stk');
        Route::post('/c2b/validate', [MpesaController::class, 'c2bValidate'])->name('c2b.validate');
        Route::post('/c2b/confirm', [MpesaController::class, 'c2bConfirm'])->name('c2b.confirm');
    });

// Flutterwave webhook — verified via HMAC header inside controller
Route::post('/webhooks/flutterwave/callback', [FlutterwaveController::class, 'webhook'])
    ->name('webhooks.flutterwave')
    ->middleware('throttle:60,1');

// Flutterwave redirect callback (GET — user returns here after card payment)
Route::get('/checkout/flutterwave/return', [FlutterwaveController::class, 'callback'])->name('checkout.flutterwave.return');

/*
|--------------------------------------------------------------------------
| Check-in Scanner (PWA-capable, mobile-first)
|--------------------------------------------------------------------------
*/
Route::prefix('scan')->name('scan.')->middleware('auth')->group(function () {
    Route::get('/{event:slug}', [CheckInScannerController::class, 'show'])->name('show');
});
