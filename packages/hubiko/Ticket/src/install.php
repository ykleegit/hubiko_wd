<?php

/**
 * Ticket Module Installation Script
 * This script is executed during the installation of the Ticket module
 */

// Run migrations
\Artisan::call('migrate', [
    '--path' => 'packages/workdo/Ticket/src/Database/Migrations',
    '--force' => true,
]);

// Run seeders
\Artisan::call('db:seed', [
    '--class' => 'Hubiko\\Ticket\\Database\\Seeders\\TicketDatabaseSeeder',
    '--force' => true,
]);

// Create module specific directories
$storage_directories = [
    'ticket-attachments',
];

foreach ($storage_directories as $dir) {
    $path = storage_path('app/public/' . $dir);
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// Add module settings
$default_settings = [
    'ticket_auto_close_days' => 7,
    'ticket_default_status' => 'open',
    'ticket_default_priority' => 'medium',
    'ticket_allow_attachments' => true,
    'ticket_max_attachment_size' => 10, // MB
    'ticket_notification_emails' => true,
];

foreach ($default_settings as $key => $value) {
    \App\Models\Utility::setModuleSettings($key, $value);
}

return true; 