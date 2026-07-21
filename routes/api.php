<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SingaPayWebhookController;
use App\Http\Controllers\HubWebhookController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;

Route::post('/webhook/singapay/invoice', [TransactionController::class, 'handleInvoice'])->withoutMiddleware(VerifyCsrfToken::class);
Route::post('/webhook/singapay/disbursement', [SingaPayWebhookController::class, 'handleDisbursement']);

// Centralized Payment Callback Hub forwards (HMAC-verified)
Route::post('/hub-webhook/singapay', [HubWebhookController::class, 'singapay'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->name('hub.webhook.singapay');

Route::get('/blogs', [BlogController::class, 'getBlogs']);
Route::post('/crm/customers/store', [HomeController::class, 'storeCustomer']);
