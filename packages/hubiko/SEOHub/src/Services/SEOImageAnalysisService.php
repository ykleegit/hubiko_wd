<?php

namespace Hubiko\SEOHub\Services;

use Illuminate\Support\Facades\Log;

class SEOImageAnalysisService
{
    /**
     * Analyze images in HTML content based on 66audit functionality
     */
    public function analyzeImages(string $html, string $url): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        $parsedUrl = parse_url($url);
        $isHttps = ($parsedUrl['scheme'] ?? '') === 'https';
        
        $images = [];
        $issues = [];
        $score = 100;
        $httpRequestsCount = 0;

        foreach ($dom->getElementsByTagName('img') as $img) {
            $src = $img->getAttribute('src');
            $type = str_starts_with($src, 'data:image/') ? 'embedded' : 'url';

            if ($type === 'url') {
                $src = $this->getFullUrlFromRelativePaths($url, $src);
                $httpRequestsCount++;
            }

            $isInternal = true;
            $srcHost = parse_url($src, PHP_URL_HOST);
            if ($srcHost !== ($parsedUrl['host'] ?? '')) {
                $isInternal = false;
            }

            $alt = $img->getAttribute('alt');
            $title = $img->getAttribute('title');
            $loading = $img->getAttribute('loading');

            $image = [
                'src' => $src,
                'alt' => $alt,
                'title' => $title,
                'loading' => $loading,
                'is_mixed_content' => $isHttps && $type === 'url' && !str_starts_with($src, 'https://'),
                'is_internal' => $isInternal,
                'type' => $type,
                'extension' => $type === 'url' ? mb_strtolower(pathinfo(strtok($src, '?'), PATHINFO_EXTENSION)) : null,
                'has_alt' => !empty($alt),
                'has_title' => !empty($title),
                'has_lazy_loading' => $loading === 'lazy',
            ];

            $images[] = $image;

            // Check for missing alt text
            if (empty($alt)) {
                $issues[] = [
                    'type' => 'accessibility',
                    'severity' => 'moderate',
                    'title' => 'Missing Alt Text',
                    'description' => 'Image is missing alt attribute',
                    'recommendation' => 'Add descriptive alt text for accessibility and SEO',
                    'element' => '<img src="' . substr($src, 0, 50) . '...">',
                ];
                $score -= 5;
            }

            // Check for mixed content
            if ($image['is_mixed_content']) {
                $issues[] = [
                    'type' => 'security',
                    'severity' => 'major',
                    'title' => 'Mixed Content Image',
                    'description' => 'HTTPS page contains HTTP image',
                    'recommendation' => 'Update image URL to use HTTPS',
                    'element' => '<img src="' . $src . '">',
                ];
                $score -= 10;
            }

            // Check for non-optimized image formats
            if ($image['extension'] && !in_array($image['extension'], ['webp', 'avif', 'jpg', 'jpeg', 'png', 'svg'])) {
                $issues[] = [
                    'type' => 'performance',
                    'severity' => 'minor',
                    'title' => 'Non-optimized Image Format',
                    'description' => "Image uses {$image['extension']} format",
                    'recommendation' => 'Consider using modern formats like WebP or AVIF',
                    'element' => '<img src="' . substr($src, 0, 50) . '...">',
                ];
                $score -= 2;
            }

            // Check for lazy loading on images below the fold
            if (!$image['has_lazy_loading'] && $type === 'url') {
                $issues[] = [
                    'type' => 'performance',
                    'severity' => 'minor',
                    'title' => 'Missing Lazy Loading',
                    'description' => 'Image could benefit from lazy loading',
                    'recommendation' => 'Add loading="lazy" attribute to improve page speed',
                    'element' => '<img src="' . substr($src, 0, 50) . '...">',
                ];
                $score -= 1;
            }
        }

        // Check for excessive image requests
        if ($httpRequestsCount > 20) {
            $issues[] = [
                'type' => 'performance',
                'severity' => 'moderate',
                'title' => 'Too Many Image Requests',
                'description' => "Page loads {$httpRequestsCount} images",
                'recommendation' => 'Consider image optimization, sprites, or lazy loading',
                'element' => '<img>',
                'details' => ['count' => $httpRequestsCount],
            ];
            $score -= 8;
        }

        // Calculate statistics
        $totalImages = count($images);
        $imagesWithAlt = count(array_filter($images, fn($img) => $img['has_alt']));
        $imagesWithLazyLoading = count(array_filter($images, fn($img) => $img['has_lazy_loading']));
        $embeddedImages = count(array_filter($images, fn($img) => $img['type'] === 'embedded'));
        $externalImages = count(array_filter($images, fn($img) => !$img['is_internal']));

        return [
            'issues' => $issues,
            'score_adjustment' => 100 - $score,
            'metrics' => [
                'total_images' => $totalImages,
                'images_with_alt' => $imagesWithAlt,
                'images_with_lazy_loading' => $imagesWithLazyLoading,
                'embedded_images' => $embeddedImages,
                'external_images' => $externalImages,
                'http_requests' => $httpRequestsCount,
                'alt_text_coverage' => $totalImages > 0 ? round(($imagesWithAlt / $totalImages) * 100, 1) : 0,
                'lazy_loading_coverage' => $totalImages > 0 ? round(($imagesWithLazyLoading / $totalImages) * 100, 1) : 0,
            ],
            'images' => $images,
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
