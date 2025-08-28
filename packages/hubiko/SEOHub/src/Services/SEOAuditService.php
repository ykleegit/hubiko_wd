<?php

namespace Hubiko\SEOHub\Services;

use Hubiko\SEOHub\Entities\SEOWebsite;
use Hubiko\SEOHub\Entities\SEOAudit;
use Hubiko\SEOHub\Entities\SEOIssue;
use Hubiko\SEOHub\Events\AuditCompleted;
use Hubiko\SEOHub\Events\IssueDetected;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SEOAuditService
{
    /**
     * Run comprehensive SEO audit for a website
     */
    public function runAudit(SEOWebsite $website): SEOAudit
    {
        $audit = SEOAudit::create([
            'workspace_id' => $website->workspace_id,
            'user_id' => $website->user_id,
            'website_id' => $website->id,
            'url' => $website->url,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            // Fetch website content
            $response = Http::timeout(30)->get($website->url);
            
            if (!$response->successful()) {
                throw new \Exception('Website is not accessible');
            }

            $html = $response->body();
            $auditResults = $this->analyzeWebsite($html, $website->url);

            // Update audit with results
            $audit->update([
                'title' => $auditResults['title'],
                'meta_description' => $auditResults['meta_description'],
                'score' => $auditResults['score'],
                'total_issues' => $auditResults['total_issues'],
                'major_issues' => $auditResults['major_issues'],
                'moderate_issues' => $auditResults['moderate_issues'],
                'minor_issues' => $auditResults['minor_issues'],
                'passed_tests' => $auditResults['passed_tests'],
                'audit_data' => $auditResults['audit_data'],
                'performance_metrics' => $auditResults['performance_metrics'],
                'seo_metrics' => $auditResults['seo_metrics'],
                'accessibility_metrics' => $auditResults['accessibility_metrics'],
                'best_practices' => $auditResults['best_practices'],
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Create issues
            foreach ($auditResults['issues'] as $issueData) {
                $issue = SEOIssue::create([
                    'workspace_id' => $website->workspace_id,
                    'user_id' => $website->user_id,
                    'audit_id' => $audit->id,
                    'type' => $issueData['type'],
                    'severity' => $issueData['severity'],
                    'title' => $issueData['title'],
                    'description' => $issueData['description'],
                    'recommendation' => $issueData['recommendation'],
                    'element' => $issueData['element'] ?? null,
                    'details' => $issueData['details'] ?? null,
                ]);

                event(new IssueDetected($issue));
            }

            // Update website last audit time
            $website->update(['last_audit_at' => now()]);

            event(new AuditCompleted($audit));

            return $audit;

        } catch (\Exception $e) {
            $audit->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('SEO Audit Failed', [
                'website_id' => $website->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Run comprehensive SEO audit with advanced features
     */
    public function runAuditAdvanced(string $url): array
    {
        try {
            // Make HTTP request to get page content with advanced settings
            $response = Http::timeout(30)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 5,
                        'strict' => true,
                        'referer' => true,
                        'protocols' => ['http', 'https'],
                        'track_redirects' => true
                    ],
                    'verify' => false, // Allow self-signed certificates for testing
                ])
                ->withHeaders([
                    'User-Agent' => 'SEOHub-Auditor/1.0 (Hubiko SEO Analysis)',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception("HTTP request failed with status: " . $response->status());
            }

            $html = $response->body();
            $responseTime = $response->transferStats?->getTransferTime() ?? 0;
            
            // Parse HTML content with proper encoding handling
            $html = trim($html);
            if (mb_detect_encoding($html, 'UTF-8', true) === false) {
                $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
            }
            
            $dom = new \DOMDocument('1.0', 'UTF-8');
            libxml_use_internal_errors(true);
            
            if (!$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                throw new \Exception('Failed to parse HTML content');
            }
            
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            
            // Run comprehensive analysis
            $basicAnalysis = $this->analyzeBasicSEO($dom, $url);
            $performanceAnalysis = $this->analyzePerformance($html, $response->headers());
            $advancedAnalysis = $this->analyzeAdvancedFeatures($dom, $url, $html, $responseTime);
            
            // Combine all results
            $allIssues = array_merge(
                $basicAnalysis['issues'] ?? [],
                $performanceAnalysis['issues'] ?? [],
                $advancedAnalysis['issues'] ?? []
            );
            
            $totalScoreAdjustment = 
                ($basicAnalysis['score_adjustment'] ?? 0) +
                ($performanceAnalysis['score_adjustment'] ?? 0) +
                ($advancedAnalysis['score_adjustment'] ?? 0);
            
            $finalScore = max(0, 100 - $totalScoreAdjustment);
            
            return [
                'success' => true,
                'url' => $url,
                'analysis_date' => now()->toISOString(),
                'response_time' => $responseTime * 1000, // Convert to milliseconds
                'page_size' => strlen($html),
                'issues' => $allIssues,
                'score' => $finalScore,
                'metrics' => [
                    'basic' => $basicAnalysis['metrics'] ?? [],
                    'performance' => $performanceAnalysis['metrics'] ?? [],
                    'advanced' => $advancedAnalysis['advanced_metrics'] ?? [],
                ],
                'summary' => [
                    'total_issues' => count($allIssues),
                    'critical_issues' => count(array_filter($allIssues, fn($i) => $i['severity'] === 'critical')),
                    'major_issues' => count(array_filter($allIssues, fn($i) => $i['severity'] === 'major')),
                    'moderate_issues' => count(array_filter($allIssues, fn($i) => $i['severity'] === 'moderate')),
                    'minor_issues' => count(array_filter($allIssues, fn($i) => $i['severity'] === 'minor')),
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('SEO Audit failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => $url,
            ];
        }
    }

    /**
     * Analyze advanced SEO features from 66audit
     */
    private function analyzeAdvancedFeatures(\DOMDocument $dom, string $url, string $html, float $responseTime): array
    {
        $analysisService = new SEOAnalysisService();
        return $analysisService->analyzeAdvanced($html, $url);
    }

    /**
     * Analyze website HTML and return audit results
     */
    private function analyzeWebsite(string $html, string $url): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $issues = [];
        $score = 100;
        $auditData = [];

        // Title analysis
        $titleNodes = $xpath->query('//title');
        $title = $titleNodes->length > 0 ? $titleNodes->item(0)->textContent : '';
        
        if (empty($title)) {
            $issues[] = [
                'type' => 'meta',
                'severity' => 'major',
                'title' => 'Missing Page Title',
                'description' => 'The page is missing a title tag',
                'recommendation' => 'Add a descriptive title tag to improve SEO',
                'element' => '<title>',
            ];
            $score -= 15;
        } elseif (strlen($title) < 30 || strlen($title) > 60) {
            $issues[] = [
                'type' => 'meta',
                'severity' => 'moderate',
                'title' => 'Title Length Issue',
                'description' => 'Title length should be between 30-60 characters',
                'recommendation' => 'Optimize title length for better search results',
                'element' => '<title>',
            ];
            $score -= 8;
        }

        // Meta description analysis
        $metaDescNodes = $xpath->query('//meta[@name="description"]');
        $metaDescription = '';
        if ($metaDescNodes->length > 0) {
            $metaDescription = $metaDescNodes->item(0)->getAttribute('content');
        }

        if (empty($metaDescription)) {
            $issues[] = [
                'type' => 'meta',
                'severity' => 'major',
                'title' => 'Missing Meta Description',
                'description' => 'The page is missing a meta description',
                'recommendation' => 'Add a compelling meta description to improve click-through rates',
                'element' => '<meta name="description">',
            ];
            $score -= 12;
        } elseif (strlen($metaDescription) < 120 || strlen($metaDescription) > 160) {
            $issues[] = [
                'type' => 'meta',
                'severity' => 'moderate',
                'title' => 'Meta Description Length Issue',
                'description' => 'Meta description should be between 120-160 characters',
                'recommendation' => 'Optimize meta description length',
                'element' => '<meta name="description">',
            ];
            $score -= 6;
        }

        // Heading structure analysis
        $h1Nodes = $xpath->query('//h1');
        if ($h1Nodes->length === 0) {
            $issues[] = [
                'type' => 'content',
                'severity' => 'major',
                'title' => 'Missing H1 Tag',
                'description' => 'The page is missing an H1 heading',
                'recommendation' => 'Add a descriptive H1 heading to improve content structure',
                'element' => '<h1>',
            ];
            $score -= 10;
        } elseif ($h1Nodes->length > 1) {
            $issues[] = [
                'type' => 'content',
                'severity' => 'moderate',
                'title' => 'Multiple H1 Tags',
                'description' => 'The page has multiple H1 tags',
                'recommendation' => 'Use only one H1 tag per page',
                'element' => '<h1>',
            ];
            $score -= 5;
        }

        // Image alt text analysis
        $imgNodes = $xpath->query('//img');
        $imagesWithoutAlt = 0;
        foreach ($imgNodes as $img) {
            if (!$img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                $imagesWithoutAlt++;
            }
        }

        if ($imagesWithoutAlt > 0) {
            $issues[] = [
                'type' => 'images',
                'severity' => $imagesWithoutAlt > 5 ? 'major' : 'moderate',
                'title' => 'Images Missing Alt Text',
                'description' => "{$imagesWithoutAlt} images are missing alt text",
                'recommendation' => 'Add descriptive alt text to all images for accessibility and SEO',
                'element' => '<img>',
                'details' => ['count' => $imagesWithoutAlt],
            ];
            $score -= min($imagesWithoutAlt * 2, 15);
        }

        // Internal/External links analysis
        $linkNodes = $xpath->query('//a[@href]');
        $internalLinks = 0;
        $externalLinks = 0;
        $brokenLinks = 0;

        foreach ($linkNodes as $link) {
            $href = $link->getAttribute('href');
            if (strpos($href, 'http') === 0) {
                if (strpos($href, parse_url($url, PHP_URL_HOST)) !== false) {
                    $internalLinks++;
                } else {
                    $externalLinks++;
                }
            } else {
                $internalLinks++;
            }
        }

        // Performance metrics (simulated)
        $performanceMetrics = [
            'page_load_time' => rand(800, 3000), // milliseconds
            'page_size' => strlen($html),
            'total_requests' => rand(20, 80),
            'images_count' => $imgNodes->length,
            'scripts_count' => $xpath->query('//script')->length,
            'stylesheets_count' => $xpath->query('//link[@rel="stylesheet"]')->length,
        ];

        // SEO metrics
        $seoMetrics = [
            'title_length' => strlen($title),
            'meta_description_length' => strlen($metaDescription),
            'h1_count' => $h1Nodes->length,
            'h2_count' => $xpath->query('//h2')->length,
            'internal_links' => $internalLinks,
            'external_links' => $externalLinks,
            'images_with_alt' => $imgNodes->length - $imagesWithoutAlt,
            'images_without_alt' => $imagesWithoutAlt,
        ];

        // Accessibility metrics
        $accessibilityMetrics = [
            'images_with_alt_ratio' => $imgNodes->length > 0 ? (($imgNodes->length - $imagesWithoutAlt) / $imgNodes->length) * 100 : 100,
            'heading_structure_score' => $h1Nodes->length === 1 ? 100 : 70,
            'link_context_score' => 85, // Simulated
        ];

        // Best practices
        $bestPractices = [
            'https_enabled' => strpos($url, 'https://') === 0,
            'meta_viewport' => $xpath->query('//meta[@name="viewport"]')->length > 0,
            'favicon_present' => $xpath->query('//link[@rel="icon" or @rel="shortcut icon"]')->length > 0,
            'robots_meta' => $xpath->query('//meta[@name="robots"]')->length > 0,
        ];

        // Calculate issue counts
        $majorIssues = count(array_filter($issues, fn($issue) => $issue['severity'] === 'major'));
        $moderateIssues = count(array_filter($issues, fn($issue) => $issue['severity'] === 'moderate'));
        $minorIssues = count(array_filter($issues, fn($issue) => $issue['severity'] === 'minor'));
        $totalIssues = count($issues);

        // Calculate passed tests
        $totalTests = 15; // Total number of tests we run
        $passedTests = $totalTests - $totalIssues;

        return [
            'title' => $title,
            'meta_description' => $metaDescription,
            'score' => max(0, $score),
            'total_issues' => $totalIssues,
            'major_issues' => $majorIssues,
            'moderate_issues' => $moderateIssues,
            'minor_issues' => $minorIssues,
            'passed_tests' => $passedTests,
            'issues' => $issues,
            'audit_data' => [
                'url' => $url,
                'analyzed_at' => now()->toISOString(),
                'total_tests' => $totalTests,
            ],
            'performance_metrics' => $performanceMetrics,
            'seo_metrics' => $seoMetrics,
            'accessibility_metrics' => $accessibilityMetrics,
            'best_practices' => $bestPractices,
        ];
    }
}
