<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\GivePermissionToRole;
use Hubiko\Ticket\Entities\TicketUtility;

class GiveRoleToPermission
{
    /**
     * Handle the event.
     */
    public function handle(GivePermissionToRole $event): void
    {
        // Get the role ID from the event
        if(isset($event->roleid))
        {
            TicketUtility::givePermissionToRoles($event->roleid);
        }
        
        // Or get the role name from the event
        if(isset($event->rolename))
        {
            TicketUtility::givePermissionToRoles(null, $event->rolename);
        }
    }
} 