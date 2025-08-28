<?php

namespace Hubiko\WhatStore\Database\Seeders;

use Illuminate\Database\Seeder;
use Hubiko\WhatStore\Entities\WhatStoreUtility;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Setup permissions for company role
        WhatStoreUtility::givePermissionToRoles(null, 'company');
        
        // Setup permissions for super admin role
        WhatStoreUtility::givePermissionToRoles(null, 'super admin');
    }
}
