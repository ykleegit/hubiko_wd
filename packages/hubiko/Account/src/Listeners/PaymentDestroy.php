<?php

namespace Hubiko\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubiko\Account\Entities\AddTransactionLine;
use Hubiko\Account\Events\DestroyPayment;

class PaymentDestroy
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

    public function handle(DestroyPayment $event)
    {
        if (module_is_active('Account')) {

            $payment = $event->payment;

            AddTransactionLine::where('reference_id',$payment->id)->where('reference', 'Payment')->delete();
        }
    }
}
