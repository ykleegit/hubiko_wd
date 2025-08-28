<?php

namespace Hubiko\WhatStore\Database\Seeders;

use Illuminate\Database\Seeder;
use Hubiko\WhatStore\Entities\WhatStoreUtility;

class WhatStoreDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Setup default data for WhatStore module
        WhatStoreUtility::defaultData(1, 1); // Default workspace and company
        
        // Setup permissions for roles
        WhatStoreUtility::givePermissionToRoles(null, 'company');
        WhatStoreUtility::givePermissionToRoles(null, 'super admin');
    }
}
