<?php

/**
 * Ticket Module Uninstallation Script
 * This script is executed during the uninstallation of the Ticket module
 */

// Rollback migrations
\Artisan::call('migrate:rollback', [
    '--path' => 'packages/workdo/Ticket/src/Database/Migrations',
    '--force' => true,
]);

// Remove module settings
$module_settings = [
    'ticket_auto_close_days',
    'ticket_default_status',
    'ticket_default_priority',
    'ticket_allow_attachments',
    'ticket_max_attachment_size',
    'ticket_notification_emails',
];

foreach ($module_settings as $key) {
    \App\Models\Utility::removeModuleSettings($key);
}

// We don't remove the attachment directory automatically to prevent data loss
// Uncomment the below code if you want to remove the directories during uninstallation
/*
$storage_directories = [
    'ticket-attachments',
];

foreach ($storage_directories as $dir) {
    $path = storage_path('app/public/' . $dir);
    if (file_exists($path)) {
        \Illuminate\Support\Facades\File::deleteDirectory($path);
    }
}
*/

return true; 