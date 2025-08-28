<?php

use Illuminate\Support\Facades\Route;
use Hubiko\SEOHub\Http\Controllers\SEODashboardController;
use Hubiko\SEOHub\Http\Controllers\SEOWebsiteController;
use Hubiko\SEOHub\Http\Controllers\SEOAuditController;
use Hubiko\SEOHub\Http\Controllers\SEOKeywordController;
use Hubiko\SEOHub\Http\Controllers\SEOIssueController;
use Hubiko\SEOHub\Http\Controllers\SEOReportController;

Route::group(['middleware' => ['web', 'auth', 'verified']], function () {
    Route::prefix('seo')->name('seo.')->group(function () {
        
        // Dashboard
        Route::get('/', [SEODashboardController::class, 'index'])->name('dashboard');
        Route::get('/chart-data', [SEODashboardController::class, 'getAuditChartData'])->name('chart.data');
        Route::get('/issues-distribution', [SEODashboardController::class, 'getIssuesDistribution'])->name('issues.distribution');
        
        // Websites
        Route::resource('websites', SEOWebsiteController::class);
        Route::post('websites/{website}/audit', [SEOWebsiteController::class, 'runAudit'])->name('websites.audit');
        Route::get('websites/{website}/settings', [SEOWebsiteController::class, 'settings'])->name('websites.settings');
        Route::put('websites/{website}/settings', [SEOWebsiteController::class, 'updateSettings'])->name('websites.settings.update');
        
        // Audits
        Route::resource('audits', SEOAuditController::class)->only(['index', 'show', 'destroy']);
        Route::post('audits/{audit}/refresh', [SEOAuditController::class, 'refresh'])->name('audits.refresh');
        Route::get('audits/{audit}/export', [SEOAuditController::class, 'export'])->name('audits.export');
        
        // Keywords
        Route::resource('keywords', SEOKeywordController::class);
        Route::post('keywords/bulk-import', [SEOKeywordController::class, 'bulkImport'])->name('keywords.bulk-import');
        Route::post('keywords/{keyword}/check-ranking', [SEOKeywordController::class, 'checkRanking'])->name('keywords.check-ranking');
        
        // Issues
        Route::resource('issues', SEOIssueController::class)->only(['index', 'show', 'update']);
        Route::post('issues/{issue}/fix', [SEOIssueController::class, 'markAsFixed'])->name('issues.fix');
        Route::post('issues/{issue}/ignore', [SEOIssueController::class, 'markAsIgnored'])->name('issues.ignore');
        Route::post('issues/bulk-action', [SEOIssueController::class, 'bulkAction'])->name('issues.bulk-action');
        
        // Reports
        Route::resource('reports', SEOReportController::class);
        Route::post('reports/{report}/generate', [SEOReportController::class, 'generate'])->name('reports.generate');
        Route::get('reports/{report}/download', [SEOReportController::class, 'download'])->name('reports.download');
        
    });
});
