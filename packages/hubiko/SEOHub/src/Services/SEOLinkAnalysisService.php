<?php

namespace Hubiko\SEOHub\Services;

use Illuminate\Support\Facades\Log;

class SEOLinkAnalysisService
{
    /**
     * Analyze links in HTML content based on 66audit functionality
     */
    public function analyzeLinks(string $html, string $url): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        $parsedUrl = parse_url($url);
        $isHttps = ($parsedUrl['scheme'] ?? '') === 'https';
        
        $links = [];
        $externalLinksCount = 0;
        $internalLinksCount = 0;
        $unsafeExternalLinksCount = 0;
        $socialLinks = [];
        $specialProtocols = ['mailto', 'tel', 'sms', 'facetime'];
        
        $issues = [];
        $score = 100;

        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = $a->getAttribute('href');
            $isInternal = true;
            $type = 'url';

            // Skip empty hrefs
            if (empty($href)) {
                continue;
            }

            // Check for special protocol schemes
            foreach ($specialProtocols as $protocol) {
                if (str_starts_with($href, $protocol . ':')) {
                    $type = $protocol;
                    break;
                }
            }

            // Check for hotlinks (anchors)
            if ($type === 'url' && str_starts_with($href, '#')) {
                $type = 'hotlink';
            }

            // Process regular URLs
            if ($type === 'url') {
                $href = $this->getFullUrlFromRelativePaths($url, $href);
                $hrefHost = parse_url($href, PHP_URL_HOST);

                if ($hrefHost !== ($parsedUrl['host'] ?? '')) {
                    $isInternal = false;
                }
            }

            // Check rel attributes
            $rel = $a->getAttribute('rel');
            $isNoreferrer = false;
            $isNoopener = false;
            
            if ($rel) {
                if (str_contains($rel, 'noreferrer')) {
                    $isNoreferrer = true;
                }
                if (str_contains($rel, 'noopener')) {
                    $isNoopener = true;
                }
            }

            $link = [
                'href' => $href,
                'title' => trim($a->getAttribute('title')),
                'text' => trim($a->textContent),
                'is_mixed_content' => $isHttps && $type === 'url' && !str_starts_with($href, 'https://'),
                'type' => $type,
                'is_internal' => $isInternal,
                'is_noopener' => $isNoopener,
                'is_noreferrer' => $isNoreferrer,
                'is_unsafe' => !$isInternal && !$isNoopener && !$isNoreferrer,
            ];

            $links[] = $link;

            // Count unsafe external links
            if ($link['is_unsafe']) {
                $unsafeExternalLinksCount++;
            }

            // Count link types
            if ($isInternal) {
                $internalLinksCount++;
            } else {
                $externalLinksCount++;
                
                // Check for social media links
                if ($this->isSocialMediaLink($href)) {
                    $socialLinks[] = $link;
                }
            }

            // Check for mixed content issues
            if ($link['is_mixed_content']) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'major',
                    'title' => 'Mixed Content Link',
                    'description' => 'HTTPS page contains HTTP link',
                    'recommendation' => 'Update link to use HTTPS',
                    'element' => '<a href="' . $href . '">',
                ];
                $score -= 10;
            }
        }

        // Check for unsafe external links
        if ($unsafeExternalLinksCount > 0) {
            $issues[] = [
                'type' => 'security',
                'severity' => 'moderate',
                'title' => 'Unsafe External Links',
                'description' => "Found {$unsafeExternalLinksCount} external links without rel=\"noopener noreferrer\"",
                'recommendation' => 'Add rel="noopener noreferrer" to external links for security',
                'element' => '<a>',
                'details' => ['count' => $unsafeExternalLinksCount],
            ];
            $score -= min(20, $unsafeExternalLinksCount * 2);
        }

        // Check link balance
        $totalLinks = $internalLinksCount + $externalLinksCount;
        if ($totalLinks > 0) {
            $externalRatio = $externalLinksCount / $totalLinks;
            if ($externalRatio > 0.3) {
                $issues[] = [
                    'type' => 'seo',
                    'severity' => 'minor',
                    'title' => 'High External Link Ratio',
                    'description' => sprintf('%.1f%% of links are external', $externalRatio * 100),
                    'recommendation' => 'Consider balancing with more internal links',
                    'element' => '<a>',
                ];
                $score -= 5;
            }
        }

        return [
            'issues' => $issues,
            'score_adjustment' => 100 - $score,
            'metrics' => [
                'total_links' => count($links),
                'internal_links' => $internalLinksCount,
                'external_links' => $externalLinksCount,
                'unsafe_external_links' => $unsafeExternalLinksCount,
                'social_links' => count($socialLinks),
                'link_types' => array_count_values(array_column($links, 'type')),
            ],
            'links' => $links,
            'social_links' => $socialLinks,
        ];
    }

    /**
     * Convert relative URLs to absolute URLs
     */
    private function getFullUrlFromRelativePaths(string $baseUrl, string $relativeUrl): string
    {
        // If already absolute URL, return as is
        if (filter_var($relativeUrl, FILTER_VALIDATE_URL)) {
            return $relativeUrl;
        }

        $parsedBase = parse_url($baseUrl);
        
        // Handle protocol-relative URLs
        if (str_starts_with($relativeUrl, '//')) {
            return ($parsedBase['scheme'] ?? 'http') . ':' . $relativeUrl;
        }

        // Handle absolute paths
        if (str_starts_with($relativeUrl, '/')) {
            return ($parsedBase['scheme'] ?? 'http') . '://' . 
                   ($parsedBase['host'] ?? '') . 
                   ($parsedBase['port'] ? ':' . $parsedBase['port'] : '') . 
                   $relativeUrl;
        }

        // Handle relative paths
        $basePath = dirname($parsedBase['path'] ?? '/');
        if ($basePath === '.') {
            $basePath = '/';
        }

        return ($parsedBase['scheme'] ?? 'http') . '://' . 
               ($parsedBase['host'] ?? '') . 
               ($parsedBase['port'] ? ':' . $parsedBase['port'] : '') . 
               rtrim($basePath, '/') . '/' . ltrim($relativeUrl, '/');
    }

    /**
     * Check if a URL is a social media link
     */
    private function isSocialMediaLink(string $url): bool
    {
        $socialDomains = [
            'facebook.com', 'www.facebook.com', 'fb.com',
            'twitter.com', 'www.twitter.com', 'x.com', 'www.x.com',
            'instagram.com', 'www.instagram.com',
            'linkedin.com', 'www.linkedin.com',
            'youtube.com', 'www.youtube.com',
            'tiktok.com', 'www.tiktok.com',
            'pinterest.com', 'www.pinterest.com',
            'snapchat.com', 'www.snapchat.com',
            'reddit.com', 'www.reddit.com',
            'tumblr.com', 'www.tumblr.com',
            'whatsapp.com', 'www.whatsapp.com',
            'telegram.org', 'www.telegram.org',
        ];

        $host = parse_url($url, PHP_URL_HOST);
        return in_array(strtolower($host ?? ''), $socialDomains);
    }
}
