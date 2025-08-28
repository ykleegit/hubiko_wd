<?php

namespace Hubiko\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubiko\Account\Entities\AccountUtility;
use Hubiko\Account\Entities\BankAccount;
use Hubiko\Account\Entities\ChartOfAccount;
use Hubiko\Account\Events\CreatePayment;

class PaymentCreate
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

    public function handle(CreatePayment $event)
    {
        if (module_is_active('Account')) {

            $payment = $event->payment;

            $account     = BankAccount::find($payment->account_id);
            $get_account = ChartOfAccount::find($account->chart_account_id);
            $data        = [
                'account_id'         => !empty($get_account)? $get_account->id : 0,
                'transaction_type'   => 'credit',
                'transaction_amount' => $payment->amount,
                'reference'          => 'Payment',
                'reference_id'       => $payment->id,
                'reference_sub_id'   => 0,
                'date'               => $payment->date,
            ];
            AccountUtility::addTransactionLines($data);

            $account = ChartOfAccount::where('name','Accounts Payable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'debit',
                'transaction_amount' => $payment->amount,
                'reference'          => 'Payment',
                'reference_id'       => $payment->id,
                'reference_sub_id'   => 0,
                'date'               => $payment->date,
            ];
            AccountUtility::addTransactionLines($data);
        }
    }
}
