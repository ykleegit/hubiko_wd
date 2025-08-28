<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\DefaultData;
use Hubiko\Ticket\Entities\TicketUtility;

class DataDefault
{
    /**
     * Handle the event.
     */
    public function handle(DefaultData $event): void
    {
        $user = $event->user;
        $company = $event->company;
        $workspace = $event->workspace;
        
        TicketUtility::defaultData($company->id, $workspace->id);
    }
} 