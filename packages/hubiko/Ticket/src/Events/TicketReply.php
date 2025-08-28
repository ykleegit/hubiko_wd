<?php

namespace Hubiko\Ticket\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReply
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversion;
    public $request;

    /**
     * Create a new event instance.
     */
    public function __construct($conversion, $request = null)
    {
        $this->conversion = $conversion;
        $this->request = $request;
    }
} 