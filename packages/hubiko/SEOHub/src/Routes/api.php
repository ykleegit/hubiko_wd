<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Hubiko\SEOHub\Http\Controllers\Api\SEOApiController;

Route::middleware(['auth:sanctum'])->prefix('seo')->name('api.seo.')->group(function () {
    
    // Website API endpoints
    Route::get('/websites', [SEOApiController::class, 'getWebsites']);
    Route::post('/websites', [SEOApiController::class, 'createWebsite']);
    Route::get('/websites/{website}', [SEOApiController::class, 'getWebsite']);
    Route::put('/websites/{website}', [SEOApiController::class, 'updateWebsite']);
    Route::delete('/websites/{website}', [SEOApiController::class, 'deleteWebsite']);
    
    // Audit API endpoints
    Route::get('/audits', [SEOApiController::class, 'getAudits']);
    Route::post('/audits', [SEOApiController::class, 'createAudit']);
    Route::get('/audits/{audit}', [SEOApiController::class, 'getAudit']);
    Route::post('/audits/{audit}/run', [SEOApiController::class, 'runAudit']);
    
    // Keywords API endpoints
    Route::get('/keywords', [SEOApiController::class, 'getKeywords']);
    Route::post('/keywords', [SEOApiController::class, 'createKeyword']);
    Route::get('/keywords/{keyword}', [SEOApiController::class, 'getKeyword']);
    Route::put('/keywords/{keyword}', [SEOApiController::class, 'updateKeyword']);
    Route::delete('/keywords/{keyword}', [SEOApiController::class, 'deleteKeyword']);
    
    // Issues API endpoints
    Route::get('/issues', [SEOApiController::class, 'getIssues']);
    Route::get('/issues/{issue}', [SEOApiController::class, 'getIssue']);
    Route::put('/issues/{issue}', [SEOApiController::class, 'updateIssue']);
    
    // Dashboard stats
    Route::get('/dashboard/stats', [SEOApiController::class, 'getDashboardStats']);
    Route::get('/dashboard/chart-data', [SEOApiController::class, 'getChartData']);
    
});
