<?php

return [
    // General
    'module_name' => 'Ticket Management',
    
    // Permissions
    'permissions' => [
        'ticket_manage' => 'Manage Tickets',
        'ticket_create' => 'Create Tickets',
        'ticket_edit' => 'Edit Tickets',
        'ticket_delete' => 'Delete Tickets',
        'ticket_show' => 'View Tickets',
        'ticket_export' => 'Export Tickets',
    ],
    
    // Form validations
    'validation' => [
        'name_required' => 'Name is required',
        'email_required' => 'Email is required',
        'subject_required' => 'Subject is required',
        'category_required' => 'Category is required',
        'priority_required' => 'Priority is required',
        'description_required' => 'Description is required',
    ],
    
    // Emails
    'email' => [
        'new_ticket_subject' => 'New Ticket Created: :ticket_id',
        'new_ticket_body' => 'A new ticket has been created with ID :ticket_id',
        'ticket_reply_subject' => 'New Reply to Ticket: :ticket_id',
        'ticket_reply_body' => 'A new reply has been added to ticket :ticket_id',
        'ticket_status_subject' => 'Ticket Status Changed: :ticket_id',
        'ticket_status_body' => 'The status of ticket :ticket_id has been changed to :status',
        'ticket_assigned_subject' => 'Ticket Assigned: :ticket_id',
        'ticket_assigned_body' => 'Ticket :ticket_id has been assigned to you',
    ],
    
    // Settings
    'settings' => [
        'enable_email_notifications' => 'Enable Email Notifications',
        'auto_assign_tickets' => 'Auto-assign Tickets',
        'default_ticket_status' => 'Default Ticket Status',
        'default_ticket_priority' => 'Default Ticket Priority',
        'allow_file_attachments' => 'Allow File Attachments',
        'max_file_size' => 'Maximum File Size (MB)',
        'allowed_file_types' => 'Allowed File Types',
    ],
    
    // Dashboard
    'dashboard' => [
        'total_tickets' => 'Total Tickets',
        'open_tickets' => 'Open Tickets',
        'closed_tickets' => 'Closed Tickets',
        'tickets_by_category' => 'Tickets by Category',
        'tickets_by_priority' => 'Tickets by Priority',
        'recent_tickets' => 'Recent Tickets',
        'ticket_stats' => 'Ticket Statistics',
    ],
    
    // Help text
    'help' => [
        'categories_help' => 'Categories help organize tickets by department or topic',
        'priorities_help' => 'Priorities determine how urgently tickets need attention',
        'attachments_help' => 'Attach files to provide more information (Max :size MB)',
    ],
]; 