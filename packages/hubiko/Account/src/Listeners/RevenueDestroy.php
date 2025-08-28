<?php

namespace Hubiko\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubiko\Account\Entities\AddTransactionLine;
use Hubiko\Account\Events\DestroyRevenue;

class RevenueDestroy
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(DestroyRevenue $event)
    {
        if (module_is_active('Account')) {

            $revenue = $event->revenue;

            AddTransactionLine::where('reference_id',$revenue->id)->where('reference', 'Revenue')->delete();
        }
    }
}
