<?php

/**
 * Script to fix all remaining packages/workdo path references
 */

$baseDir = __DIR__;
$filesToUpdate = [
    'app/Console/Commands/MakePackageComponent.php',
    'app/Console/Commands/PackageSeed.php', 
    'app/Console/Commands/CreatePackage.php',
    'app/Providers/PackageServiceProvider.php',
    'app/Classes/Module.php',
    'app/Http/Controllers/LanguageController.php',
    'app/Http/Controllers/HomeController.php',
    'app/Http/Controllers/ModuleController.php'
];

echo "Fixing remaining packages/workdo path references...\n\n";

foreach ($filesToUpdate as $file) {
    $filePath = $baseDir . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "Skipping {$file} - file not found\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Replace all packages/workdo references with packages/hubiko
    $content = str_replace('packages/workdo', 'packages/hubiko', $content);
    
    // Replace Workdo namespace references with Hubiko
    $content = str_replace('Workdo\\', 'Hubiko\\', $content);
    
    // Replace specific workdo references in URLs and paths
    $content = str_replace('/workdo/', '/hubiko/', $content);
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "✓ Updated: {$file}\n";
    } else {
        echo "- No changes needed: {$file}\n";
    }
}

echo "\nAll path references have been updated from packages/workdo to packages/hubiko!\n";
