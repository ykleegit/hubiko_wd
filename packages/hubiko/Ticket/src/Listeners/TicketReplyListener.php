<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\TicketReply;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Hubiko\Ticket\Entities\Ticket;

class TicketReplyListener implements ShouldQueue
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
    public function handle(TicketReply $event): void
    {
        $conversion = $event->conversion;
        $request = $event->request;
        
        // Get the ticket associated with this reply
        $ticket = Ticket::find($conversion->ticket_id);
        
        if ($ticket) {
            // Log the ticket reply for audit purposes
            Log::info('Ticket reply added', [
                'ticket_id' => $ticket->id,
                'conversion_id' => $conversion->id,
                'replied_by' => $conversion->sender,
                'workspace' => $conversion->workspace
            ]);
            
            // If the ticket is in "New Ticket" status, update it to "In Progress"
            if ($ticket->status == 'New Ticket') {
                $ticket->status = 'In Progress';
                $ticket->save();
            }
            
            // Additional processing can be added here:
            // - Update activity timestamps
            // - Send notifications
            // - Update metrics and analytics
        }
    }
} 