<?php

namespace Hubiko\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubiko\Account\Entities\AddTransactionLine;
use Hubiko\Account\Events\PaymentDestroyBill;
use Hubiko\Account\Entities\TransactionLines;

class BillPaymentDestroy
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

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(PaymentDestroyBill $event)
    {
        if (module_is_active('Account')) {

            $bill        = $event->bill;
            $billPayment = $event->payment;

            AddTransactionLine::where('reference_id',$bill->id)->where('reference_sub_id',$billPayment->id)->where('reference', 'Bill Payment')->delete();
        }
    }
}
