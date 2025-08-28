<?php

namespace Hubiko\Ticket;

class Ticket
{
    /**
     * Get all tickets for the current workspace
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTickets()
    {
        return \Hubiko\Ticket\Entities\Ticket::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->get();
    }
    
    /**
     * Get a ticket by ID
     * 
     * @param int $id
     * @return \Hubiko\Ticket\Entities\Ticket
     */
    public function getTicket($id)
    {
        return \Hubiko\Ticket\Entities\Ticket::find($id);
    }
    
    /**
     * Get count of tickets by status
     * 
     * @param string $status
     * @return int
     */
    public function countTicketsByStatus($status)
    {
        return \Hubiko\Ticket\Entities\Ticket::where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->where('status', $status)
            ->count();
    }
    
    /**
     * Check if module is active
     * 
     * @return bool
     */
    public function isActive()
    {
        return module_is_active('Ticket');
    }
} 