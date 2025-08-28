<?php

namespace Hubiko\SEOHub\Services;

use Hubiko\SEOHub\Entities\SEOWebsite;
use Hubiko\SEOHub\Entities\SEOAudit;
use Hubiko\SEOHub\Entities\SEOIssue;
use Hubiko\SEOHub\Entities\SEOKeyword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SEOIntegrationService
{
    /**
     * Sync audit results with CRM system
     */
    public function syncAuditWithCRM(SEOAudit $audit)
    {
        try {
            // If EcommerceHub is active, create lead/opportunity from SEO insights
            if (module_is_active('EcommerceHub')) {
                $this->createCRMOpportunityFromAudit($audit);
            }

            // Log activity in CRM
            $this->logCRMActivity($audit);

        } catch (\Exception $e) {
            Log::error('SEO CRM Integration Error: ' . $e->getMessage());
        }
    }

    /**
     * Create CRM opportunity from audit results
     */
    private function createCRMOpportunityFromAudit(SEOAudit $audit)
    {
        // Create opportunity based on SEO issues found
        $majorIssues = $audit->major_issues;
        $totalIssues = $audit->total_issues;

        if ($majorIssues > 0 || $totalIssues > 10) {
            // High potential for SEO services
            $opportunityData = [
                'title' => "SEO Optimization for {$audit->website->name}",
                'description' => "Website audit revealed {$majorIssues} major issues and {$totalIssues} total issues. SEO score: {$audit->score}/100",
                'value' => $this->calculateOpportunityValue($audit),
                'source' => 'SEO Audit',
                'website_url' => $audit->url,
                'audit_id' => $audit->id,
            ];

            // Create opportunity in CRM (integrate with existing CRM module)
            event('crm.opportunity.created', $opportunityData);
        }
    }

    /**
     * Calculate opportunity value based on audit results
     */
    private function calculateOpportunityValue(SEOAudit $audit)
    {
        $baseValue = 500; // Base SEO service value
        $issueMultiplier = $audit->total_issues * 50;
        $scoreMultiplier = (100 - $audit->score) * 10;

        return min($baseValue + $issueMultiplier + $scoreMultiplier, 5000);
    }

    /**
     * Log CRM activity
     */
    private function logCRMActivity(SEOAudit $audit)
    {
        $activityData = [
            'type' => 'seo_audit',
            'title' => "SEO Audit Completed: {$audit->website->name}",
            'description' => "Audit completed with score {$audit->score}/100. Found {$audit->total_issues} issues.",
            'user_id' => $audit->user_id,
            'workspace_id' => $audit->workspace_id,
            'related_type' => 'seo_audit',
            'related_id' => $audit->id,
        ];

        event('crm.activity.logged', $activityData);
    }

    /**
     * Send audit completed notification
     */
    public function sendAuditCompletedNotification(SEOAudit $audit)
    {
        try {
            $user = \App\Models\User::find($audit->user_id);
            
            if ($user) {
                $notificationData = [
                    'title' => 'SEO Audit Completed',
                    'message' => "Audit for {$audit->website->name} completed with score {$audit->score}/100",
                    'url' => route('seo.audits.show', $audit->id),
                    'type' => 'seo_audit_completed',
                ];

                // Send notification using Hubiko's notification system
                event('notification.send', [$user, $notificationData]);
            }

        } catch (\Exception $e) {
            Log::error('SEO Notification Error: ' . $e->getMessage());
        }
    }

    /**
     * Schedule next audit for website
     */
    public function scheduleNextAudit(SEOWebsite $website)
    {
        $settings = $website->settings ?? [];
        $frequency = $settings['audit_frequency'] ?? 'weekly';

        $nextAuditDate = match($frequency) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            default => now()->addWeek(),
        };

        $website->update(['next_audit_at' => $nextAuditDate]);
    }

    /**
     * Schedule initial audit for new website
     */
    public function scheduleInitialAudit(SEOWebsite $website)
    {
        // Schedule audit to run in 5 minutes
        $website->update(['next_audit_at' => now()->addMinutes(5)]);

        // Dispatch audit job
        dispatch(new \Hubiko\SEOHub\Jobs\RunSEOAuditJob($website));
    }

    /**
     * Setup default keywords for website
     */
    public function setupDefaultKeywords(SEOWebsite $website)
    {
        // Extract potential keywords from website content
        $defaultKeywords = $this->extractKeywordsFromWebsite($website);

        foreach ($defaultKeywords as $keyword) {
            SEOKeyword::create([
                'workspace_id' => $website->workspace_id,
                'user_id' => $website->user_id,
                'website_id' => $website->id,
                'keyword' => $keyword,
                'status' => 'tracking',
            ]);
        }
    }

    /**
     * Extract keywords from website
     */
    private function extractKeywordsFromWebsite(SEOWebsite $website)
    {
        // Basic keyword extraction (can be enhanced with AI/ML)
        $websiteName = $website->name;
        $domain = parse_url($website->url, PHP_URL_HOST);
        
        $keywords = [];
        
        // Add brand keywords
        $keywords[] = $websiteName;
        $keywords[] = str_replace(['.com', '.net', '.org'], '', $domain);
        
        // Add common business keywords based on domain
        if (str_contains($domain, 'shop') || str_contains($domain, 'store')) {
            $keywords[] = $websiteName . ' shop';
            $keywords[] = $websiteName . ' store';
        }

        return array_unique($keywords);
    }

    /**
     * Send critical issue alert
     */
    public function sendCriticalIssueAlert(SEOIssue $issue)
    {
        try {
            $user = \App\Models\User::find($issue->user_id);
            
            if ($user) {
                $notificationData = [
                    'title' => 'Critical SEO Issue Detected',
                    'message' => "Major issue found: {$issue->title}",
                    'url' => route('seo.issues.show', $issue->id),
                    'type' => 'seo_critical_issue',
                    'priority' => 'high',
                ];

                event('notification.send', [$user, $notificationData]);
            }

        } catch (\Exception $e) {
            Log::error('SEO Critical Alert Error: ' . $e->getMessage());
        }
    }

    /**
     * Log issue for reporting
     */
    public function logIssueForReporting(SEOIssue $issue)
    {
        // Log issue data for analytics and reporting
        Log::info('SEO Issue Detected', [
            'issue_id' => $issue->id,
            'type' => $issue->type,
            'severity' => $issue->severity,
            'website_id' => $issue->audit->website_id,
            'audit_id' => $issue->audit_id,
            'workspace_id' => $issue->workspace_id,
        ]);
    }

    /**
     * Integrate with EcommerceHub for product SEO
     */
    public function syncWithEcommerceHub(SEOWebsite $website)
    {
        if (!module_is_active('EcommerceHub')) {
            return;
        }

        try {
            // Get e-commerce products that need SEO optimization
            $products = \Hubiko\EcommerceHub\Entities\EcommerceProduct::where('workspace_id', $website->workspace_id)
                ->whereNull('seo_optimized_at')
                ->limit(10)
                ->get();

            foreach ($products as $product) {
                // Create keywords for product
                $productKeywords = [
                    $product->name,
                    $product->name . ' buy online',
                    $product->name . ' price',
                    $product->category->name ?? 'product',
                ];

                foreach ($productKeywords as $keyword) {
                    SEOKeyword::firstOrCreate([
                        'workspace_id' => $website->workspace_id,
                        'user_id' => $website->user_id,
                        'website_id' => $website->id,
                        'keyword' => $keyword,
                    ], [
                        'status' => 'tracking',
                        'target_url' => route('ecommerce.products.show', $product->id),
                    ]);
                }

                // Mark product as SEO processed
                $product->update(['seo_optimized_at' => now()]);
            }

        } catch (\Exception $e) {
            Log::error('SEO EcommerceHub Integration Error: ' . $e->getMessage());
        }
    }
}
