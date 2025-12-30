<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\SalesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Payment Routes
Route::middleware(['auth:sanctum'])->prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate'])->name('api.payment.initiate');
    Route::get('/verify/{reference}', [PaymentController::class, 'verify'])->name('api.payment.verify');
});

// Paystack Webhook (Public, signature verified in controller)
Route::post('/paystack/webhook', [WebhookController::class, 'handle'])->name('api.paystack.webhook');

// Product Stock API (used in POS) - already exists but ensuring it's accessible via API route if needed
// Route::get('/product/stock', [SalesController::class, 'getProductStock'])->name('api.product.stock'); // This is currently in web.php likely, but if moved to API:
