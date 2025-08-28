<?php

use Illuminate\Support\Facades\Route;
use Hubiko\EcommerceHub\Http\Controllers\EcommerceDashboardController;
use Hubiko\EcommerceHub\Http\Controllers\EcommerceStoreController;
use Hubiko\EcommerceHub\Http\Controllers\EcommerceProductController;
use Hubiko\EcommerceHub\Http\Controllers\EcommerceOrderController;
use Hubiko\EcommerceHub\Http\Controllers\EcommerceCustomerController;
use Hubiko\EcommerceHub\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web', 'auth', 'verified']], function () {
    Route::prefix('ecommerce')->name('ecommerce.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [EcommerceDashboardController::class, 'index'])->name('dashboard');
        Route::get('/store/{store}/overview', [EcommerceDashboardController::class, 'storeOverview'])->name('store.overview');
        
        // Stores Management
        Route::resource('stores', EcommerceStoreController::class);
        Route::post('stores/{store}/toggle-status', [EcommerceStoreController::class, 'toggleStatus'])->name('stores.toggle-status');
        
        // Products Management
        Route::resource('products', EcommerceProductController::class);
        Route::post('products/{product}/toggle-status', [EcommerceProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('products/bulk-action', [EcommerceProductController::class, 'bulkAction'])->name('products.bulk-action');
        
        // Orders Management
        Route::resource('orders', EcommerceOrderController::class);
        Route::post('orders/{order}/update-status', [EcommerceOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('orders/{order}/update-payment-status', [EcommerceOrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
        Route::get('orders/{order}/invoice', [EcommerceOrderController::class, 'generateInvoice'])->name('orders.invoice');
        
        // Customers Management
        Route::resource('customers', EcommerceCustomerController::class);
        Route::get('customers/{customer}/orders', [EcommerceCustomerController::class, 'orders'])->name('customers.orders');
        
        // Payment Processing
        Route::post('payment/process', [PaymentController::class, 'process'])->name('payment.process');
        Route::post('payment/webhook/{gateway}', [PaymentController::class, 'webhook'])->name('payment.webhook');
        Route::get('payment/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
        
    });
});

// Public storefront routes (no auth required)
Route::prefix('store')->name('store.')->group(function () {
    Route::get('/{slug}', [EcommerceStoreController::class, 'storefront'])->name('show');
    Route::get('/{slug}/product/{product}', [EcommerceProductController::class, 'show'])->name('product.show');
    Route::post('/{slug}/cart/add', [EcommerceOrderController::class, 'addToCart'])->name('cart.add');
    Route::get('/{slug}/cart', [EcommerceOrderController::class, 'cart'])->name('cart');
    Route::post('/{slug}/checkout', [EcommerceOrderController::class, 'checkout'])->name('checkout');
});
