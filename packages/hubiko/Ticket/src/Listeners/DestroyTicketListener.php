<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\DestroyTicket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DestroyTicketListener implements ShouldQueue
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
    public function handle(DestroyTicket $event): void
    {
        $ticket = $event->ticket;
        
        // Log the ticket deletion for audit purposes
        Log::info('Ticket deleted', [
            'ticket_id' => $ticket->id,
            'deleted_by' => auth()->id(),
            'workspace' => $ticket->workspace,
            'subject' => $ticket->subject
        ]);
        
        // Additional cleanup or notification tasks can be performed here:
        // - Notify relevant users about the deletion
        // - Update statistics or reports
        // - Archive ticket data if needed
    }
} 