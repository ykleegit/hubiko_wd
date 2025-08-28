<?php

/**
 * Get the localized ticket status name
 *
 * @param string $status
 * @return string
 */
function get_ticket_status_name($status)
{
    $statuses = [
        'open' => __('Open'),
        'in_progress' => __('In Progress'),
        'on_hold' => __('On Hold'),
        'closed' => __('Closed')
    ];
    
    return $statuses[$status] ?? $status;
}

/**
 * Get the localized ticket priority name
 *
 * @param string $priority
 * @return string
 */
function get_ticket_priority_name($priority)
{
    $priorities = [
        'low' => __('Low'),
        'medium' => __('Medium'),
        'high' => __('High'),
        'critical' => __('Critical')
    ];
    
    return $priorities[$priority] ?? $priority;
}

/**
 * Check if the Ticket module is active
 *
 * @return bool
 */
function is_ticket_module_active()
{
    return module_is_active('Ticket');
} 