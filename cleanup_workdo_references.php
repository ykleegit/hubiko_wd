<?php

/**
 * Script to remove all Workdo.io references from the codebase
 */

$baseDir = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'REF'];

function removeWorkdoReferences($directory, $excludeDirs = []) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $changedFiles = [];
    
    foreach ($iterator as $file) {
        $filePath = $file->getPathname();
        
        // Skip excluded directories
        $skip = false;
        foreach ($excludeDirs as $excludeDir) {
            if (strpos($filePath, DIRECTORY_SEPARATOR . $excludeDir . DIRECTORY_SEPARATOR) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;
        
        // Only process text files
        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['php', 'blade', 'js', 'json', 'md', 'txt', 'html', 'css'])) {
            continue;
        }
        
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Remove Workdo.io references
        $replacements = [
            // URLs and domains
            'https://hubiko.com' => 'https://hubiko.com',
            'https://hubiko.com' => 'https://hubiko.com',
            'hubiko.com' => 'hubiko.com',
            'www.hubiko.com' => 'www.hubiko.com',
            
            // Email addresses
            'support@hubiko.com' => 'support@hubiko.com',
            'info@hubiko.com' => 'info@hubiko.com',
            'contact@hubiko.com' => 'contact@hubiko.com',
            
            // Company references
            '"Hubiko"' => '"Hubiko"',
            "'Hubiko'" => "'Hubiko'",
            'Hubiko Team' => 'Hubiko Team',
            'Hubiko Inc' => 'Hubiko Inc',
            'Hubiko LLC' => 'Hubiko LLC',
            
            // Marketplace and external references
            'Self-Built Add-ons' => 'Self-Built Add-ons',
            'Manage Add-ons' => 'Manage Add-ons',
            'Available Add-ons' => 'Available Add-ons',
            'Self-Built Modules' => 'Self-Built Modules',
            
            // Remove external marketplace links
            'href="https://hubiko.com/product-category/dash/"' => 'href="#" style="display:none;"',
            'href="https://hubiko.com/product-category/bundles/"' => 'href="#" style="display:none;"',
        ];
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Remove entire sections that reference external marketplace
        $patterns = [
            // Remove marketplace promotion sections
            '/<div[^>]*class="[^"]*marketplace[^"]*"[^>]*>.*?<\/div>/s',
            '/<section[^>]*class="[^"]*marketplace[^"]*"[^>]*>.*?<\/section>/s',
            
            // Remove external buy buttons
            '/<!--.*?workdo\.io.*?-->/s',
            '/\/\*.*?workdo\.io.*?\*\//s',
        ];
        
        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $changedFiles[] = $filePath;
            echo "Updated: " . str_replace($baseDir, '', $filePath) . "\n";
        }
    }
    
    return $changedFiles;
}

echo "Starting Workdo.io reference cleanup...\n\n";

$changedFiles = removeWorkdoReferences($baseDir, $excludeDirs);

echo "\nCleanup completed!\n";
echo "Total files updated: " . count($changedFiles) . "\n";

if (!empty($changedFiles)) {
    echo "\nFiles modified:\n";
    foreach ($changedFiles as $file) {
        echo "- " . str_replace($baseDir, '', $file) . "\n";
    }
}
