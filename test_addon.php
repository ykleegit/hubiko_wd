<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test add-ons
$addonManager = new App\Services\AddOnManager();

// Function to test an add-on
function testAddOn($addonManager, $addonName) {
    echo "\n===== Testing $addonName Add-on =====\n";
    
    try {
        echo "Installing $addonName...\n";
        $result = $addonManager->installAddOn($addonName);
        echo "$addonName installation result: " . ($result ? "Success" : "Failed") . "\n";
        
        echo "Enabling $addonName...\n";
        $result = $addonManager->enableAddOn($addonName);
        echo "$addonName enabling result: " . ($result ? "Success" : "Failed") . "\n";
        
        return true;
    } catch (Exception $e) {
        echo "Error with $addonName: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
        return false;
    }
}

// Test each add-on
$addons = ['AIContent', 'EcommerceHub', 'SEOHub'];
$results = [];

foreach ($addons as $addon) {
    $results[$addon] = testAddOn($addonManager, $addon);
}

// Check the status of all add-ons
echo "\n===== Final Status of All Add-ons =====\n";
$addons = $addonManager->getAvailableAddOns();
foreach ($addons as $name => $addon) {
    echo "$name: " . 
         "Installed: " . ($addon['installed'] ? 'Yes' : 'No') . ", " .
         "Enabled: " . ($addon['enabled'] ? 'Yes' : 'No') . "\n";
}

echo "\n===== Test Results Summary =====\n";
foreach ($results as $addon => $success) {
    echo "$addon: " . ($success ? "SUCCESS" : "FAILED") . "\n";
}
