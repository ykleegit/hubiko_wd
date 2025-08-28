<?php

/**
 * Migration script to convert Workdo add-ons to self-built Hubiko add-ons
 */

$workdoPath = __DIR__ . '/packages/workdo';
$hubikoPath = __DIR__ . '/packages/hubiko';

// Ensure hubiko directory exists
if (!is_dir($hubikoPath)) {
    mkdir($hubikoPath, 0755, true);
}

// Get all workdo add-ons
$workdoAddons = array_filter(glob($workdoPath . '/*'), 'is_dir');

foreach ($workdoAddons as $addonPath) {
    $addonName = basename($addonPath);
    $targetPath = $hubikoPath . '/' . $addonName;
    
    echo "Migrating {$addonName}...\n";
    
    // Copy the entire addon
    if (!is_dir($targetPath)) {
        exec("cp -r '{$addonPath}' '{$targetPath}'");
    }
    
    // Update composer.json
    $composerPath = $targetPath . '/composer.json';
    if (file_exists($composerPath)) {
        $composer = json_decode(file_get_contents($composerPath), true);
        
        // Update package name and namespace
        $composer['name'] = 'hubiko/' . strtolower($addonName);
        $composer['description'] = "Self-built {$addonName} package for Hubiko";
        
        // Update autoload namespace
        if (isset($composer['autoload']['psr-4'])) {
            $oldNamespace = "Workdo\\{$addonName}\\";
            $newNamespace = "Hubiko\\{$addonName}\\";
            
            if (isset($composer['autoload']['psr-4'][$oldNamespace])) {
                $composer['autoload']['psr-4'][$newNamespace] = $composer['autoload']['psr-4'][$oldNamespace];
                unset($composer['autoload']['psr-4'][$oldNamespace]);
            }
        }
        
        // Update service providers
        if (isset($composer['extra']['laravel']['providers'])) {
            foreach ($composer['extra']['laravel']['providers'] as &$provider) {
                $provider = str_replace('Workdo\\', 'Hubiko\\', $provider);
            }
        }
        
        // Update author
        $composer['authors'] = [
            [
                'name' => 'Hubiko',
                'email' => 'support@hubiko.com'
            ]
        ];
        
        file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    // Update module.json
    $modulePath = $targetPath . '/module.json';
    if (file_exists($modulePath)) {
        $module = json_decode(file_get_contents($modulePath), true);
        
        // Add self-built flag
        $module['self_built'] = true;
        $module['provider'] = 'hubiko';
        
        // Enhance description if empty
        if (empty($module['description'])) {
            $module['description'] = "Self-built {$addonName} module with enhanced features and local management";
        }
        
        file_put_contents($modulePath, json_encode($module, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    // Update PHP files to use new namespace
    updateNamespaceInFiles($targetPath, 'Workdo', 'Hubiko');
    
    echo "✓ {$addonName} migrated successfully\n";
}

echo "\nMigration completed! All add-ons have been converted to self-built Hubiko add-ons.\n";

function updateNamespaceInFiles($directory, $oldNamespace, $newNamespace) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            
            // Replace namespace declarations
            $content = preg_replace(
                '/namespace\s+' . preg_quote($oldNamespace, '/') . '\\\\([^;]+);/',
                'namespace ' . $newNamespace . '\\\\$1;',
                $content
            );
            
            // Replace use statements
            $content = preg_replace(
                '/use\s+' . preg_quote($oldNamespace, '/') . '\\\\([^;]+);/',
                'use ' . $newNamespace . '\\\\$1;',
                $content
            );
            
            // Replace class references
            $content = str_replace($oldNamespace . '\\', $newNamespace . '\\', $content);
            
            file_put_contents($file->getPathname(), $content);
        }
    }
}
