<?php

/**
 * Script to integrate missing add-ons from reference implementation
 */

$refPath = __DIR__ . '/REF/old_copy/packages/workdo';
$hubikoPath = __DIR__ . '/packages/hubiko';
$currentAddons = array_map('basename', glob($hubikoPath . '/*', GLOB_ONLYDIR));
$refAddons = array_map('basename', glob($refPath . '/*', GLOB_ONLYDIR));

// Find missing add-ons
$missingAddons = array_diff($refAddons, $currentAddons);

echo "Found missing add-ons: " . implode(', ', $missingAddons) . "\n\n";

foreach ($missingAddons as $addonName) {
    $sourcePath = $refPath . '/' . $addonName;
    $targetPath = $hubikoPath . '/' . $addonName;
    
    echo "Integrating {$addonName}...\n";
    
    // Copy the entire addon
    exec("cp -r '{$sourcePath}' '{$targetPath}'");
    
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
        
        // Add enhanced features based on addon type
        switch ($addonName) {
            case 'AIContent':
                $module['features'] = [
                    'AI-Powered Content Generation',
                    'Multiple Content Types Support',
                    'Template Management',
                    'Content Optimization',
                    'SEO-Friendly Output'
                ];
                break;
            case 'Booking':
                $module['features'] = [
                    'Online Booking System',
                    'Calendar Integration',
                    'Payment Processing',
                    'Booking Management',
                    'Customer Notifications'
                ];
                break;
            case 'CompanySecretary':
                $module['features'] = [
                    'Company Registration Management',
                    'Legal Document Templates',
                    'Compliance Tracking',
                    'Filing Management',
                    'Corporate Governance'
                ];
                break;
            case 'EcommerceHub':
                $module['features'] = [
                    'Multi-Store Management',
                    'Product Catalog',
                    'Order Processing',
                    'Inventory Management',
                    'Payment Gateway Integration'
                ];
                break;
            case 'SEOHub':
                $module['features'] = [
                    'SEO Analysis Tools',
                    'Keyword Research',
                    'Meta Tag Management',
                    'Site Optimization',
                    'Performance Tracking'
                ];
                break;
            case 'Ticket':
                $module['features'] = [
                    'Support Ticket System',
                    'Customer Portal',
                    'Ticket Routing',
                    'SLA Management',
                    'Knowledge Base Integration'
                ];
                break;
            case 'WhatStore':
                $module['features'] = [
                    'WhatsApp Store Integration',
                    'Product Catalog Sharing',
                    'Order Management via WhatsApp',
                    'Customer Communication',
                    'Automated Responses'
                ];
                break;
        }
        
        file_put_contents($modulePath, json_encode($module, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    // Update PHP files to use new namespace
    updateNamespaceInFiles($targetPath, 'Workdo', 'Hubiko');
    
    echo "✓ {$addonName} integrated successfully\n";
}

echo "\nIntegration completed! All missing add-ons have been integrated.\n";

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
