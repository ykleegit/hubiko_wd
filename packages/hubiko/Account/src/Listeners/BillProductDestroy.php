<?php

namespace Hubiko\Account\Listeners;

// use App\Events\ProductDestroyBill;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubiko\Account\Entities\AddTransactionLine;
use Hubiko\Account\Entities\BillAccount;
use Hubiko\Account\Entities\TransactionLines;
use Hubiko\Account\Events\ProductDestroyBill;

class BillProductDestroy
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
    public function handle(ProductDestroyBill $event)
    {
        if (module_is_active('Account')) {

            $billProduct = $event->bill;
            AddTransactionLine::where('reference_id', $billProduct->bill_id)->where('reference_sub_id', $billProduct->product_id)->where('reference', 'Bill')->delete();
        }
    }
}
