<?php

use Illuminate\Support\Facades\Route;
use Hubiko\WhatStore\Http\Controllers\HealthController;
use Hubiko\WhatStore\Http\Controllers\Api\ProductController;
use Hubiko\WhatStore\Http\Controllers\Api\OrderController;
use Hubiko\WhatStore\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Health check endpoints (no authentication required)
Route::prefix('whatstore/health')->group(function () {
    Route::get('/', [HealthController::class, 'index']);
    Route::get('/whatsapp', [HealthController::class, 'whatsapp']);
    Route::get('/database', [HealthController::class, 'database']);
    Route::get('/payment-gateways', [HealthController::class, 'paymentGateways']);
    Route::get('/cache', [HealthController::class, 'cache']);
});

// API routes requiring authentication
Route::middleware(['auth:sanctum'])->prefix('whatstore')->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);
    
    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
    
    // Customers
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/orders', [CustomerController::class, 'orders']);
});

// Webhook endpoint for WhatsApp (uses specific middleware)
Route::post('whatstore/webhook', 'Hubiko\WhatStore\Http\Controllers\WebhookController@handle')
    ->middleware('whatstore.webhook'); 