<?php

namespace Hubiko\SEOHub\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SEOAnalysisService
{
    /**
     * Advanced SEO analysis based on 66audit functionality
     */
    public function analyzeAdvanced(string $html, string $url): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $issues = [];
        $score = 100;
        $parsedUrl = parse_url($url);

        // Use specialized analysis services
        $linkAnalysis = (new SEOLinkAnalysisService())->analyzeLinks($html, $url);
        $imageAnalysis = (new SEOImageAnalysisService())->analyzeImages($html, $url);
        $scriptAnalysis = (new SEOScriptAnalysisService())->analyzeScripts($html, $url);

        // Combine issues from all analyses
        $issues = array_merge(
            $issues,
            $linkAnalysis['issues'] ?? [],
            $imageAnalysis['issues'] ?? [],
            $scriptAnalysis['issues'] ?? []
        );

        // Combine score adjustments
        $score -= ($linkAnalysis['score_adjustment'] ?? 0);
        $score -= ($imageAnalysis['score_adjustment'] ?? 0);
        $score -= ($scriptAnalysis['score_adjustment'] ?? 0);

        // SEO-friendly URL check
        $isSeoFriendlyUrl = $this->checkSeoFriendlyUrl($parsedUrl['path'] ?? '');
        if (!$isSeoFriendlyUrl) {
            $issues[] = [
                'type' => 'url',
                'severity' => 'moderate',
                'title' => 'Non-SEO Friendly URL',
                'description' => 'URL contains non-SEO friendly characters',
                'recommendation' => 'Use lowercase letters, numbers, and hyphens only in URLs',
                'element' => $url,
            ];
            $score -= 8;
        }

        // Deprecated HTML tags check
        $deprecatedTags = $this->getDeprecatedHtmlTags();
        foreach ($deprecatedTags as $tag) {
            $nodes = $xpath->query("//{$tag}");
            if ($nodes->length > 0) {
                $issues[] = [
                    'type' => 'html',
                    'severity' => 'minor',
                    'title' => "Deprecated HTML Tag: {$tag}",
                    'description' => "Found {$nodes->length} deprecated <{$tag}> tag(s)",
                    'recommendation' => "Replace deprecated <{$tag}> tags with modern alternatives",
                    'element' => "<{$tag}>",
                    'details' => ['count' => $nodes->length],
                ];
                $score -= 2;
            }
        }

        // Meta robots check
        $robotsNodes = $xpath->query('//meta[@name="robots"]');
        $robotsContent = '';
        if ($robotsNodes->length > 0) {
            $robotsContent = $robotsNodes->item(0)->getAttribute('content');
            if (strpos($robotsContent, 'noindex') !== false) {
                $issues[] = [
                    'type' => 'meta',
                    'severity' => 'major',
                    'title' => 'Page Set to NoIndex',
                    'description' => 'Page has noindex directive in robots meta tag',
                    'recommendation' => 'Remove noindex directive if you want this page to be indexed',
                    'element' => '<meta name="robots">',
                ];
                $score -= 20;
            }
        }

        // Canonical URL check
        $canonicalNodes = $xpath->query('//link[@rel="canonical"]');
        if ($canonicalNodes->length === 0) {
            $issues[] = [
                'type' => 'meta',
                'severity' => 'moderate',
                'title' => 'Missing Canonical URL',
                'description' => 'Page is missing a canonical URL',
                'recommendation' => 'Add a canonical URL to prevent duplicate content issues',
                'element' => '<link rel="canonical">',
            ];
            $score -= 6;
        } elseif ($canonicalNodes->length > 1) {
            $issues[] = [
                'type' => 'meta',
                'severity' => 'moderate',
                'title' => 'Multiple Canonical URLs',
                'description' => 'Page has multiple canonical URLs',
                'recommendation' => 'Use only one canonical URL per page',
                'element' => '<link rel="canonical">',
            ];
            $score -= 8;
        }

        // Open Graph tags check
        $ogTitleNodes = $xpath->query('//meta[@property="og:title"]');
        $ogDescNodes = $xpath->query('//meta[@property="og:description"]');
        $ogImageNodes = $xpath->query('//meta[@property="og:image"]');

        if ($ogTitleNodes->length === 0) {
            $issues[] = [
                'type' => 'social',
                'severity' => 'minor',
                'title' => 'Missing Open Graph Title',
                'description' => 'Page is missing og:title meta tag',
                'recommendation' => 'Add Open Graph title for better social media sharing',
                'element' => '<meta property="og:title">',
            ];
            $score -= 3;
        }

        if ($ogDescNodes->length === 0) {
            $issues[] = [
                'type' => 'social',
                'severity' => 'minor',
                'title' => 'Missing Open Graph Description',
                'description' => 'Page is missing og:description meta tag',
                'recommendation' => 'Add Open Graph description for better social media sharing',
                'element' => '<meta property="og:description">',
            ];
            $score -= 3;
        }

        if ($ogImageNodes->length === 0) {
            $issues[] = [
                'type' => 'social',
                'severity' => 'minor',
                'title' => 'Missing Open Graph Image',
                'description' => 'Page is missing og:image meta tag',
                'recommendation' => 'Add Open Graph image for better social media sharing',
                'element' => '<meta property="og:image">',
            ];
            $score -= 3;
        }

        // Twitter Card tags check
        $twitterCardNodes = $xpath->query('//meta[@name="twitter:card"]');
        if ($twitterCardNodes->length === 0) {
            $issues[] = [
                'type' => 'social',
                'severity' => 'minor',
                'title' => 'Missing Twitter Card',
                'description' => 'Page is missing Twitter Card meta tags',
                'recommendation' => 'Add Twitter Card tags for better Twitter sharing',
                'element' => '<meta name="twitter:card">',
            ];
            $score -= 2;
        }

        // Schema.org structured data check
        $schemaNodes = $xpath->query('//script[@type="application/ld+json"]');
        if ($schemaNodes->length === 0) {
            $issues[] = [
                'type' => 'structured_data',
                'severity' => 'moderate',
                'title' => 'Missing Structured Data',
                'description' => 'Page has no JSON-LD structured data',
                'recommendation' => 'Add structured data to help search engines understand your content',
                'element' => '<script type="application/ld+json">',
            ];
            $score -= 7;
        }

        // Page speed indicators
        $scriptNodes = $xpath->query('//script');
        $cssNodes = $xpath->query('//link[@rel="stylesheet"]');
        $inlineStyleNodes = $xpath->query('//style');

        if ($scriptNodes->length > 10) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'moderate',
                'title' => 'Too Many Script Tags',
                'description' => "Page has {$scriptNodes->length} script tags",
                'recommendation' => 'Consider combining and minifying JavaScript files',
                'element' => '<script>',
                'details' => ['count' => $scriptNodes->length],
            ];
            $score -= 5;
        }

        if ($cssNodes->length > 5) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'minor',
                'title' => 'Multiple CSS Files',
                'description' => "Page loads {$cssNodes->length} CSS files",
                'recommendation' => 'Consider combining CSS files to reduce HTTP requests',
                'element' => '<link rel="stylesheet">',
                'details' => ['count' => $cssNodes->length],
            ];
            $score -= 3;
        }

        if ($inlineStyleNodes->length > 0) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'minor',
                'title' => 'Inline Styles Found',
                'description' => "Page has {$inlineStyleNodes->length} inline style blocks",
                'recommendation' => 'Move inline styles to external CSS files',
                'element' => '<style>',
                'details' => ['count' => $inlineStyleNodes->length],
            ];
            $score -= 2;
        }

        // Content analysis
        $textContent = $dom->textContent;
        $wordCount = str_word_count(strip_tags($textContent));

        if ($wordCount < 300) {
            $issues[] = [
                'type' => 'content',
                'severity' => 'moderate',
                'title' => 'Low Content Word Count',
                'description' => "Page has only {$wordCount} words",
                'recommendation' => 'Add more quality content (aim for at least 300 words)',
                'element' => 'body',
                'details' => ['word_count' => $wordCount],
            ];
            $score -= 10;
        }

        // Language declaration check
        $htmlNodes = $xpath->query('//html[@lang]');
        if ($htmlNodes->length === 0) {
            $issues[] = [
                'type' => 'accessibility',
                'severity' => 'moderate',
                'title' => 'Missing Language Declaration',
                'description' => 'HTML element is missing lang attribute',
                'recommendation' => 'Add lang attribute to html element (e.g., lang="en")',
                'element' => '<html>',
            ];
            $score -= 6;
        }

        return [
            'issues' => $issues,
            'score_adjustment' => 100 - max(0, $score),
            'advanced_metrics' => [
                'seo_friendly_url' => $isSeoFriendlyUrl,
                'deprecated_tags_count' => count(array_filter($issues, fn($i) => $i['type'] === 'html')),
                'social_tags_present' => [
                    'og_title' => $ogTitleNodes->length > 0,
                    'og_description' => $ogDescNodes->length > 0,
                    'og_image' => $ogImageNodes->length > 0,
                    'twitter_card' => $twitterCardNodes->length > 0,
                ],
                'structured_data_present' => $schemaNodes->length > 0,
                'word_count' => $wordCount,
                'resource_counts' => [
                    'scripts' => $scriptNodes->length,
                    'stylesheets' => $cssNodes->length,
                    'inline_styles' => $inlineStyleNodes->length,
                ],
                'link_metrics' => $linkAnalysis['metrics'] ?? [],
                'image_metrics' => $imageAnalysis['metrics'] ?? [],
                'script_metrics' => $scriptAnalysis['metrics'] ?? [],
            ],
        ];
    }

    /**
     * Check if URL is SEO-friendly
     */
    private function checkSeoFriendlyUrl(string $path): bool
    {
        return preg_match('/^[a-z0-9\-\/]*$/', $path);
    }

    /**
     * Get list of deprecated HTML tags
     */
    private function getDeprecatedHtmlTags(): array
    {
        return [
            'acronym', 'applet', 'basefont', 'bgsound', 'big', 'blink',
            'center', 'command', 'content', 'dir', 'element', 'font',
            'frame', 'frameset', 'isindex', 'keygen', 'marquee', 'menuitem',
            'nobr', 'noembed', 'noframes', 'plaintext', 'shadow', 'spacer',
            'strike', 'tt', 'u', 'xmp'
        ];
    }
}
