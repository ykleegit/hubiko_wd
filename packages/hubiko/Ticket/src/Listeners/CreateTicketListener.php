<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\CreateTicket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateTicketListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreateTicket $event): void
    {
        $ticket = $event->ticket;
        $request = $event->request;
        
        // Log the ticket creation for audit purposes
        Log::info('Ticket created', [
            'ticket_id' => $ticket->id,
            'created_by' => $ticket->created_by,
            'workspace' => $ticket->workspace,
            'subject' => $ticket->subject,
            'category' => $ticket->category_id,
            'priority' => $ticket->priority,
            'assigned_to' => $ticket->is_assign,
        ]);
        
        // Additional processing can be added here:
        // - Send notifications to other systems
        // - Update statistics 
        // - Trigger workflow automations
    }
} 