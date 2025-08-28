<?php

namespace Hubiko\WhatStore\Listeners;

use App\Events\GivePermissionToRole;
use Hubiko\WhatStore\Entities\WhatStoreUtility;

class GiveRoleToPermission
{
    /**
     * Handle the event.
     *
     * @param GivePermissionToRole $event
     * @return void
     */
    public function handle(GivePermissionToRole $event): void
    {
        $role_id = $event->role_id;
        $rolename = $event->rolename;

        // Assign module permissions to roles
        WhatStoreUtility::givePermissionToRoles($role_id, $rolename);
    }
} 