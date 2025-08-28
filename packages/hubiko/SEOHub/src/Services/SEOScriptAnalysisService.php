<?php

namespace Hubiko\SEOHub\Services;

use Illuminate\Support\Facades\Log;

class SEOScriptAnalysisService
{
    /**
     * Analyze JavaScript and CSS resources based on 66audit functionality
     */
    public function analyzeScripts(string $html, string $url): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        $parsedUrl = parse_url($url);
        $isHttps = ($parsedUrl['scheme'] ?? '') === 'https';
        
        $scripts = [];
        $stylesheets = [];
        $schemas = [];
        $issues = [];
        $score = 100;
        
        $httpRequestsData = [
            'js' => 0,
            'css' => 0,
        ];
        
        $nonDeferredScriptsCount = 0;

        // Analyze JavaScript files
        foreach ($dom->getElementsByTagName('script') as $script) {
            $src = $script->getAttribute('src');
            $type = $src ? 'url' : 'embedded';

            if ($type === 'url') {
                $src = $this->getFullUrlFromRelativePaths($url, $src);
                $httpRequestsData['js']++;
            }

            $isInternal = true;
            $srcHost = parse_url($src, PHP_URL_HOST);
            if ($srcHost && $srcHost !== ($parsedUrl['host'] ?? '')) {
                $isInternal = false;
            }

            $scriptData = [
                'src' => $src,
                'is_mixed_content' => $isHttps && $type === 'url' && !str_starts_with($src, 'https://'),
                'is_deferred' => (bool) $script->getAttribute('defer'),
                'is_async' => (bool) $script->getAttribute('async'),
                'is_internal' => $isInternal,
                'type' => $type,
                'content_length' => $type === 'embedded' ? strlen($script->nodeValue) : null,
            ];

            $scripts[] = $scriptData;

            // Count non-deferred scripts
            if (!$scriptData['is_deferred'] && !$scriptData['is_async']) {
                $nonDeferredScriptsCount++;
            }

            // Check for mixed content
            if ($scriptData['is_mixed_content']) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'major',
                    'title' => 'Mixed Content Script',
                    'description' => 'HTTPS page contains HTTP script',
                    'recommendation' => 'Update script URL to use HTTPS',
                    'element' => '<script src="' . $src . '">',
                ];
                $score -= 15;
            }

            // Detect JSON-LD schemas
            if ($script->getAttribute('type') === 'application/ld+json') {
                $parsedSchema = json_decode($script->nodeValue, true);
                if ($parsedSchema) {
                    $schemas[] = $parsedSchema;
                }
            }
        }

        // Analyze CSS files
        foreach ($dom->getElementsByTagName('link') as $link) {
            $rel = $link->getAttribute('rel');
            
            if ($rel === 'stylesheet') {
                $href = $link->getAttribute('href');
                $href = $this->getFullUrlFromRelativePaths($url, $href);
                $httpRequestsData['css']++;

                $isInternal = true;
                $hrefHost = parse_url($href, PHP_URL_HOST);
                if ($hrefHost && $hrefHost !== ($parsedUrl['host'] ?? '')) {
                    $isInternal = false;
                }

                $stylesheetData = [
                    'href' => $href,
                    'media' => $link->getAttribute('media') ?: 'all',
                    'is_mixed_content' => $isHttps && !str_starts_with($href, 'https://'),
                    'is_internal' => $isInternal,
                ];

                $stylesheets[] = $stylesheetData;

                // Check for mixed content
                if ($stylesheetData['is_mixed_content']) {
                    $issues[] = [
                        'type' => 'security',
                        'severity' => 'major',
                        'title' => 'Mixed Content Stylesheet',
                        'description' => 'HTTPS page contains HTTP stylesheet',
                        'recommendation' => 'Update stylesheet URL to use HTTPS',
                        'element' => '<link rel="stylesheet" href="' . $href . '">',
                    ];
                    $score -= 10;
                }
            }
        }

        // Check for excessive script requests
        if ($httpRequestsData['js'] > 10) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'moderate',
                'title' => 'Too Many JavaScript Files',
                'description' => "Page loads {$httpRequestsData['js']} JavaScript files",
                'recommendation' => 'Consider combining and minifying JavaScript files',
                'element' => '<script>',
                'details' => ['count' => $httpRequestsData['js']],
            ];
            $score -= 8;
        }

        // Check for excessive CSS requests
        if ($httpRequestsData['css'] > 5) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'minor',
                'title' => 'Multiple CSS Files',
                'description' => "Page loads {$httpRequestsData['css']} CSS files",
                'recommendation' => 'Consider combining CSS files to reduce HTTP requests',
                'element' => '<link rel="stylesheet">',
                'details' => ['count' => $httpRequestsData['css']],
            ];
            $score -= 5;
        }

        // Check for non-deferred scripts
        if ($nonDeferredScriptsCount > 3) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'moderate',
                'title' => 'Non-deferred Scripts',
                'description' => "{$nonDeferredScriptsCount} scripts are blocking page rendering",
                'recommendation' => 'Add defer or async attributes to non-critical scripts',
                'element' => '<script>',
                'details' => ['count' => $nonDeferredScriptsCount],
            ];
            $score -= 6;
        }

        // Check for inline styles
        $inlineStyles = [];
        foreach ($dom->getElementsByTagName('*') as $element) {
            $style = $element->getAttribute('style');
            if (!empty($style)) {
                $inlineStyles[] = [
                    'tag' => $element->tagName,
                    'style' => $style,
                ];
            }
        }

        if (count($inlineStyles) > 5) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'minor',
                'title' => 'Excessive Inline Styles',
                'description' => count($inlineStyles) . ' elements have inline styles',
                'recommendation' => 'Move inline styles to external CSS files',
                'element' => 'style attribute',
                'details' => ['count' => count($inlineStyles)],
            ];
            $score -= 3;
        }

        // Check for structured data
        if (empty($schemas)) {
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

        return [
            'issues' => $issues,
            'score_adjustment' => 100 - $score,
            'metrics' => [
                'total_scripts' => count($scripts),
                'external_scripts' => count(array_filter($scripts, fn($s) => !$s['is_internal'])),
                'deferred_scripts' => count(array_filter($scripts, fn($s) => $s['is_deferred'])),
                'async_scripts' => count(array_filter($scripts, fn($s) => $s['is_async'])),
                'embedded_scripts' => count(array_filter($scripts, fn($s) => $s['type'] === 'embedded')),
                'total_stylesheets' => count($stylesheets),
                'external_stylesheets' => count(array_filter($stylesheets, fn($s) => !$s['is_internal'])),
                'inline_styles' => count($inlineStyles),
                'structured_data_schemas' => count($schemas),
                'http_requests' => $httpRequestsData,
            ],
            'scripts' => $scripts,
            'stylesheets' => $stylesheets,
            'schemas' => $schemas,
            'inline_styles' => $inlineStyles,
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
}
