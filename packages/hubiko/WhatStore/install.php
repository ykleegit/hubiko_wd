<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Hubiko\WhatStore\Entities\WhatStoreUtility;

// Run database migrations
try {
    Artisan::call('migrate', [
        '--path' => 'packages/workdo/WhatStore/Database/Migrations',
        '--force' => true
    ]);
    echo "✓ WhatStore migrations completed successfully.\n";
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
}

// Run seeders
try {
    Artisan::call('db:seed', [
        '--class' => 'Hubiko\\WhatStore\\Database\\Seeders\\WhatStoreDataSeeder',
        '--force' => true
    ]);
    echo "✓ WhatStore data seeded successfully.\n";
} catch (Exception $e) {
    echo "✗ Seeding failed: " . $e->getMessage() . "\n";
}

// Create storage directories
$storagePaths = [
    'app/public/whatstore',
    'app/public/whatstore/products',
    'app/public/whatstore/categories',
    'app/public/whatstore/conversations',
];

foreach ($storagePaths as $path) {
    $fullPath = storage_path($path);
    if (!File::exists($fullPath)) {
        File::makeDirectory($fullPath, 0755, true);
        echo "✓ Created storage directory: {$path}\n";
    }
}

// Set default module settings
try {
    $settings = [
        'whatstore_enabled' => 'on',
        'whatstore_currency' => 'USD',
        'whatstore_tax_rate' => '0',
        'whatstore_shipping_enabled' => 'on',
        'whatstore_guest_checkout' => 'on',
    ];

    foreach ($settings as $key => $value) {
        \DB::table('settings')->updateOrInsert(
            ['name' => $key],
            ['value' => $value, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]
        );
    }
    echo "✓ WhatStore default settings configured.\n";
} catch (Exception $e) {
    echo "✗ Settings configuration failed: " . $e->getMessage() . "\n";
}

echo "WhatStore module installation completed!\n";
