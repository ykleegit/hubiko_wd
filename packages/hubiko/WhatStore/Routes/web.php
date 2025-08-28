<?php

use Illuminate\Support\Facades\Route;
use Hubiko\WhatStore\Http\Controllers\PaymentController;
use Hubiko\WhatStore\Http\Controllers\WebhookController;
use Hubiko\WhatStore\Http\Controllers\WhatStoreController;
use Hubiko\WhatStore\Http\Controllers\ProductController;
use Hubiko\WhatStore\Http\Controllers\OrderController;
use Hubiko\WhatStore\Http\Controllers\CustomerController;
use Hubiko\WhatStore\Http\Controllers\SettingsController;
use Hubiko\WhatStore\Http\Controllers\ReportController;
use Hubiko\WhatStore\Http\Controllers\HealthDashboardController;
use Modules\WhatStore\Http\Controllers\TemplateController;
use Modules\WhatStore\Http\Controllers\CampaignController;
use Modules\WhatStore\Http\Controllers\ChatController;
use Modules\WhatStore\Http\Controllers\ReplyController;

/*
|--------------------------------------------------------------------------
| WhatStore Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the WhatStore module. 
| These routes are loaded by the WhatStoreServiceProvider.
|
*/

// Payment Routes
Route::group(['prefix' => 'whatstore/payments', 'as' => 'whatstore.'], function() {
    // Stripe Routes
    Route::post('stripe/process', [PaymentController::class, 'processStripePayment'])->name('stripe.process');
    Route::get('stripe/success', [PaymentController::class, 'stripeSuccess'])->name('stripe.success');
    Route::get('stripe/cancel', [PaymentController::class, 'stripeCancel'])->name('stripe.cancel');
    
    // PayPal Routes
    Route::post('paypal/process', [PaymentController::class, 'processPaypalPayment'])->name('paypal.process');
    Route::get('paypal/success', [PaymentController::class, 'paypalSuccess'])->name('paypal.success');
    Route::get('paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('paypal.cancel');
});

// Webhook routes - no middleware since external services need access
Route::prefix('webhooks')->name('whatstore.webhooks.')->group(function () {
    Route::post('/stripe', [\Hubiko\WhatStore\Http\Controllers\WebhookController::class, 'stripeWebhook'])->name('stripe');
    Route::post('/paypal', [\Hubiko\WhatStore\Http\Controllers\WebhookController::class, 'paypalWebhook'])->name('paypal');
});

// Routes that require authentication, verified email, and module access
Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:WhatStore'])->group(function () {
    // Dashboard
    Route::get('whatstore/dashboard', [WhatStoreController::class, 'dashboard'])->name('whatstore.dashboard');
    
    // Products
    Route::resource('whatstore/products', ProductController::class)->names([
        'index' => 'whatstore.products.index',
        'create' => 'whatstore.products.create',
        'store' => 'whatstore.products.store',
        'show' => 'whatstore.products.show',
        'edit' => 'whatstore.products.edit',
        'update' => 'whatstore.products.update',
        'destroy' => 'whatstore.products.destroy',
    ]);
    
    // Orders
    Route::resource('whatstore/orders', OrderController::class)->names([
        'index' => 'whatstore.orders.index',
        'create' => 'whatstore.orders.create',
        'store' => 'whatstore.orders.store',
        'show' => 'whatstore.orders.show',
        'edit' => 'whatstore.orders.edit',
        'update' => 'whatstore.orders.update',
        'destroy' => 'whatstore.orders.destroy',
    ]);
    Route::put('whatstore/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('whatstore.orders.update-status');
    
    // Customers
    Route::resource('whatstore/customers', CustomerController::class)->names([
        'index' => 'whatstore.customers.index',
        'create' => 'whatstore.customers.create',
        'store' => 'whatstore.customers.store',
        'show' => 'whatstore.customers.show',
        'edit' => 'whatstore.customers.edit',
        'update' => 'whatstore.customers.update',
        'destroy' => 'whatstore.customers.destroy',
    ]);
    
    // Settings
    Route::get('whatstore/settings', [SettingsController::class, 'index'])->name('whatstore.settings.index');
    Route::post('whatstore/settings', [SettingsController::class, 'update'])->name('whatstore.settings.update');
    Route::get('whatstore/settings/whatsapp', [SettingsController::class, 'whatsapp'])->name('whatstore.settings.whatsapp');
    Route::post('whatstore/settings/whatsapp', [SettingsController::class, 'updateWhatsapp'])->name('whatstore.settings.whatsapp.update');
    Route::get('whatstore/settings/payment', [SettingsController::class, 'payment'])->name('whatstore.settings.payment');
    Route::post('whatstore/settings/payment', [SettingsController::class, 'updatePayment'])->name('whatstore.settings.payment.update');
    
    // Reports
    Route::get('whatstore/reports/sales', [ReportController::class, 'sales'])->name('whatstore.reports.sales');
    Route::get('whatstore/reports/products', [ReportController::class, 'products'])->name('whatstore.reports.products');
    Route::get('whatstore/reports/customers', [ReportController::class, 'customers'])->name('whatstore.reports.customers');
    
    // WhatsBox Integration Routes
    // Templates
    Route::resource('whatstore/templates', TemplateController::class)->names([
        'index' => 'whatstore.templates.index',
        'create' => 'whatstore.templates.create',
        'store' => 'whatstore.templates.store',
        'show' => 'whatstore.templates.show',
        'edit' => 'whatstore.templates.edit',
        'update' => 'whatstore.templates.update',
        'destroy' => 'whatstore.templates.destroy',
    ]);
    Route::post('whatstore/templates/{template}/duplicate', [TemplateController::class, 'duplicate'])->name('whatstore.templates.duplicate');
    Route::post('whatstore/templates/{template}/submit', [TemplateController::class, 'submitForApproval'])->name('whatstore.templates.submit');
    
    // Campaigns
    Route::resource('whatstore/campaigns', CampaignController::class)->names([
        'index' => 'whatstore.campaigns.index',
        'create' => 'whatstore.campaigns.create',
        'store' => 'whatstore.campaigns.store',
        'show' => 'whatstore.campaigns.show',
        'edit' => 'whatstore.campaigns.edit',
        'update' => 'whatstore.campaigns.update',
        'destroy' => 'whatstore.campaigns.destroy',
    ]);
    Route::post('whatstore/campaigns/{campaign}/launch', [CampaignController::class, 'launch'])->name('whatstore.campaigns.launch');
    Route::post('whatstore/campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('whatstore.campaigns.pause');
    Route::post('whatstore/campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('whatstore.campaigns.resume');
    Route::post('whatstore/campaigns/{campaign}/cancel', [CampaignController::class, 'cancel'])->name('whatstore.campaigns.cancel');
    Route::post('whatstore/campaigns/{campaign}/duplicate', [CampaignController::class, 'duplicate'])->name('whatstore.campaigns.duplicate');
    Route::get('whatstore/campaigns/{campaign}/analytics', [CampaignController::class, 'analytics'])->name('whatstore.campaigns.analytics');
    Route::post('whatstore/campaigns/{campaign}/test', [CampaignController::class, 'test'])->name('whatstore.campaigns.test');
    
    // Chat & Messages
    Route::get('whatstore/chat', [ChatController::class, 'index'])->name('whatstore.chat.index');
    Route::get('whatstore/chat/{customer}', [ChatController::class, 'show'])->name('whatstore.chat.show');
    Route::post('whatstore/chat/{customer}/message', [ChatController::class, 'sendMessage'])->name('whatstore.chat.send');
    Route::post('whatstore/chat/{customer}/note', [ChatController::class, 'addNote'])->name('whatstore.chat.note');
    Route::post('whatstore/chat/{customer}/toggle-subscription', [ChatController::class, 'toggleSubscription'])->name('whatstore.chat.toggle-subscription');
    Route::post('whatstore/chat/{customer}/toggle-bot', [ChatController::class, 'toggleBot'])->name('whatstore.chat.toggle-bot');
    Route::get('whatstore/chat/api/statistics', [ChatController::class, 'statistics'])->name('whatstore.chat.statistics');
    Route::get('whatstore/chat/api/recent-messages', [ChatController::class, 'recentMessages'])->name('whatstore.chat.recent-messages');
    Route::get('whatstore/chat/api/search', [ChatController::class, 'search'])->name('whatstore.chat.search');
    
    // Auto Replies
    Route::resource('whatstore/replies', ReplyController::class)->names([
        'index' => 'whatstore.replies.index',
        'create' => 'whatstore.replies.create',
        'store' => 'whatstore.replies.store',
        'show' => 'whatstore.replies.show',
        'edit' => 'whatstore.replies.edit',
        'update' => 'whatstore.replies.update',
        'destroy' => 'whatstore.replies.destroy',
    ]);
    Route::post('whatstore/replies/{reply}/toggle', [ReplyController::class, 'toggle'])->name('whatstore.replies.toggle');
    Route::post('whatstore/replies/{reply}/test', [ReplyController::class, 'test'])->name('whatstore.replies.test');
    Route::post('whatstore/replies/{reply}/duplicate', [ReplyController::class, 'duplicate'])->name('whatstore.replies.duplicate');
});

// Health Dashboard (admin only)
Route::middleware(['web', 'auth', 'verified', 'admin'])->group(function () {
    Route::get('whatstore/health-dashboard', function () {
        return view('whatstore::admin.health-dashboard');
    })->name('whatstore.health-dashboard');
});

// Routes for API health check
Route::name('api.')->prefix('api')->group(function () {
    Route::get('whatstore/health', [Hubiko\WhatStore\Http\Controllers\HealthController::class, 'index'])->name('whatstore.health');
    Route::get('whatstore/health/whatsapp', [Hubiko\WhatStore\Http\Controllers\HealthController::class, 'whatsapp'])->name('whatstore.health.whatsapp');
    Route::get('whatstore/health/database', [Hubiko\WhatStore\Http\Controllers\HealthController::class, 'database'])->name('whatstore.health.database');
    Route::get('whatstore/health/payment-gateways', [Hubiko\WhatStore\Http\Controllers\HealthController::class, 'paymentGateways'])->name('whatstore.health.payment-gateways');
    Route::get('whatstore/health/cache', [Hubiko\WhatStore\Http\Controllers\HealthController::class, 'cache'])->name('whatstore.health.cache');
}); 