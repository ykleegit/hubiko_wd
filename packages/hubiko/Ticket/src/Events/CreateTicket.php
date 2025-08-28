<?php

namespace Hubiko\Ticket\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateTicket
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $request;

    /**
     * Create a new event instance.
     */
    public function __construct($ticket, $request = null)
    {
        $this->ticket = $ticket;
        $this->request = $request;
    }
} 