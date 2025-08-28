<?php

return [
    'name' => 'Ticket',
    'description' => 'Ticket Management System for Hubiko SaaS platform',
    'version' => '1.0.0',
    
    // Basic module settings
    'settings' => [
        'ticketing_enabled' => true,
        'allow_customer_tickets' => true,
        'allow_file_uploads' => true,
        'max_file_size' => 10, // in MB
        'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar,txt',
    ],
    
    // Notification settings
    'notifications' => [
        'notify_admin_on_new_ticket' => true,
        'notify_admin_on_ticket_reply' => true,
        'notify_customer_on_ticket_status_change' => true,
        'notify_agent_on_ticket_assignment' => true,
    ],
    
    // Status options
    'statuses' => [
        'New Ticket',
        'In Progress',
        'On Hold',
        'Closed',
        'Resolved',
    ],
    
    // Permission groups
    'permissions' => [
        'ticket' => [
            'manage',
            'create',
            'edit',
            'delete',
            'reply',
            'export',
        ],
        'ticket category' => [
            'manage',
            'create',
            'edit',
            'delete',
        ],
        'ticket priority' => [
            'manage',
            'create',
            'edit',
            'delete',
        ],
        'ticket customfield' => [
            'manage',
            'create',
            'edit',
            'delete',
        ],
    ],
]; 