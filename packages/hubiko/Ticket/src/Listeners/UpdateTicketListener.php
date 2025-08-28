<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\UpdateTicket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateTicketListener implements ShouldQueue
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
    public function handle(UpdateTicket $event): void
    {
        $ticket = $event->ticket;
        $request = $event->request;
        
        // Log the ticket update for audit purposes
        Log::info('Ticket updated', [
            'ticket_id' => $ticket->id,
            'updated_by' => auth()->id(),
            'workspace' => $ticket->workspace,
            'subject' => $ticket->subject,
            'category' => $ticket->category_id,
            'priority' => $ticket->priority,
            'assigned_to' => $ticket->is_assign,
            'status' => $ticket->status
        ]);
        
        // Additional processing can be added here:
        // - Send notifications to users
        // - Update statistics
        // - Trigger workflow automations
    }
} 