<?php

use Illuminate\Support\Facades\Route;
use Hubiko\AIContent\Http\Controllers\AIDashboardController;
use Hubiko\AIContent\Http\Controllers\AIContentController;
use Hubiko\AIContent\Http\Controllers\AITemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web', 'auth', 'verified']], function () {
    Route::prefix('ai-content')->name('ai-content.')->group(function () {
        
        // Dashboard
        Route::get('/', [AIDashboardController::class, 'index'])->name('dashboard');
        Route::get('/usage-chart-data', [AIDashboardController::class, 'getUsageChartData'])->name('usage.chart');
        Route::get('/content-type-data', [AIDashboardController::class, 'getContentTypeData'])->name('content-type.data');
        
        // AI Content Management
        Route::resource('content', AIContentController::class)->except(['create', 'store']);
        Route::get('content/create', [AIContentController::class, 'create'])->name('content.create');
        Route::post('content', [AIContentController::class, 'store'])->name('content.store');
        Route::post('content/{content}/regenerate', [AIContentController::class, 'regenerate'])->name('content.regenerate');
        Route::post('content/{content}/publish', [AIContentController::class, 'publish'])->name('content.publish');
        
        // AI Templates Management
        Route::resource('templates', AITemplateController::class, ['as' => 'ai']);
        Route::post('templates/{template}/toggle-status', [AITemplateController::class, 'toggleStatus'])->name('templates.toggle-status');
        
    });
});

// Alternative route names for backward compatibility
Route::group(['middleware' => ['web', 'auth', 'verified']], function () {
    Route::prefix('ai-content')->group(function () {
        Route::get('/dashboard', [AIDashboardController::class, 'index'])->name('ai-content.dashboard');
        Route::get('/content/{content}', [AIContentController::class, 'show'])->name('ai-content.show');
        Route::resource('ai-templates', AITemplateController::class);
    });
});
