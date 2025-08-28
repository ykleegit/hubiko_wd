<?php

namespace Hubiko\WhatStore\Listeners;

use App\Events\DefaultData;
use Hubiko\WhatStore\Entities\WhatStoreUtility;

class DataDefault
{
    /**
     * Handle the event to set up default data.
     *
     * @param DefaultData $event
     * @return void
     */
    public function handle(DefaultData $event): void
    {
        $company_id = $event->company_id;
        $workspace_id = $event->workspace_id;

        // Set up default data for the module
        WhatStoreUtility::defaultData($company_id, $workspace_id);
    }
} 