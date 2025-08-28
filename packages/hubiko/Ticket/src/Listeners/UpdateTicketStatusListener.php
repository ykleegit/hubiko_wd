<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\UpdateTicketStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateTicketStatusListener implements ShouldQueue
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
    public function handle(UpdateTicketStatus $event): void
    {
        $ticket = $event->ticket;
        $request = $event->request;
        
        // Log the ticket status update for audit purposes
        Log::info('Ticket status updated', [
            'ticket_id' => $ticket->id,
            'updated_by' => auth()->id(),
            'workspace' => $ticket->workspace,
            'old_status' => $ticket->getOriginal('status') ?? 'Unknown',
            'new_status' => $ticket->status,
        ]);
        
        // Update ticket metrics based on status
        if ($ticket->status == 'Resolved') {
            // Calculate resolution time
            $created_at = $ticket->created_at;
            $resolved_at = $ticket->reslove_at;
            
            if ($created_at && $resolved_at) {
                $resolution_time = $created_at->diffInMinutes($resolved_at);
                
                // Log resolution time for reporting
                Log::info('Ticket resolved', [
                    'ticket_id' => $ticket->id,
                    'resolution_time_minutes' => $resolution_time
                ]);
            }
        }
        
        // Additional processing can be added here:
        // - Send status change notifications
        // - Trigger automations based on new status
        // - Update dashboards
    }
} 