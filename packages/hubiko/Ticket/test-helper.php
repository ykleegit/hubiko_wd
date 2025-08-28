<?php
/**
 * Ticket Module Test Helper
 * 
 * This script helps verify the basic installation and functionality of the Ticket module.
 * Run this script from the command line using: php test-helper.php
 */

// Check if module is registered in database
function checkModuleRegistration() {
    echo "Checking if Ticket module is registered...\n";
    
    try {
        // This would need to be run in a Laravel context with database access
        // In a real implementation, this would use Laravel's DB facade
        echo "NOTE: This is a placeholder function. In actual use, this would query the database.\n";
        echo "SELECT * FROM add_ons WHERE module = 'Ticket';\n";
        echo "✓ Module registration check placeholder\n";
    } catch (\Exception $e) {
        echo "✗ Error checking module registration: " . $e->getMessage() . "\n";
    }
}

// Check if migrations have been run
function checkMigrations() {
    echo "Checking if migrations have been run...\n";
    
    $requiredTables = [
        'tickets',
        'ticket_conversations',
        'ticket_categories',
        'ticket_priorities'
    ];
    
    echo "The following tables should exist:\n";
    foreach ($requiredTables as $table) {
        echo "- $table\n";
    }
    
    echo "In a real implementation, this would run:\n";
    echo "Schema::hasTable('tickets');\n";
    echo "✓ Migration check placeholder\n";
}

// Check if permissions have been seeded
function checkPermissions() {
    echo "Checking if permissions have been seeded...\n";
    
    $requiredPermissions = [
        'ticket manage',
        'ticket create',
        'ticket edit',
        'ticket delete',
        'ticket show',
        'ticket reply',
        'ticket category manage',
        'ticket priority manage',
        'ticket settings'
    ];
    
    echo "The following permissions should exist:\n";
    foreach ($requiredPermissions as $permission) {
        echo "- $permission\n";
    }
    
    echo "In a real implementation, this would run:\n";
    echo "Permission::where('module', 'Ticket')->get();\n";
    echo "✓ Permission check placeholder\n";
}

// Test file structure
function checkFileStructure() {
    echo "Checking module file structure...\n";
    
    $requiredDirectories = [
        'src',
        'src/Database',
        'src/Database/Migrations',
        'src/Database/Seeders',
        'src/Entities',
        'src/Http',
        'src/Http/Controllers',
        'src/Providers',
        'src/Resources',
        'src/Resources/views',
        'src/Routes'
    ];
    
    $errors = 0;
    foreach ($requiredDirectories as $dir) {
        if (is_dir(__DIR__ . '/' . $dir)) {
            echo "✓ $dir exists\n";
        } else {
            echo "✗ $dir missing\n";
            $errors++;
        }
    }
    
    $requiredFiles = [
        'composer.json',
        'module.json',
        'README.md',
        'src/Providers/TicketServiceProvider.php',
        'src/Routes/web.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "✓ $file exists\n";
        } else {
            echo "✗ $file missing\n";
            $errors++;
        }
    }
    
    if ($errors === 0) {
        echo "✓ All required directories and files exist\n";
    } else {
        echo "✗ $errors problems found in file structure\n";
    }
}

// Run all checks
echo "=== Ticket Module Test Helper ===\n\n";
checkFileStructure();
echo "\n";
checkModuleRegistration();
echo "\n";
checkMigrations();
echo "\n";
checkPermissions();
echo "\n";
echo "=== Test helper completed ===\n";
echo "NOTE: This script provides basic verification only. Complete testing should follow the TESTING.md checklist.\n"; 