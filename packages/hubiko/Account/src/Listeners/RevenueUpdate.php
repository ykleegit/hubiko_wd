<?php

namespace Hubiko\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubiko\Account\Entities\AccountUtility;
use Hubiko\Account\Entities\BankAccount;
use Hubiko\Account\Entities\ChartOfAccount;
use Hubiko\Account\Events\UpdateRevenue;

class RevenueUpdate
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

    public function handle(UpdateRevenue $event)
    {
        if (module_is_active('Account')) {

            $revenue = $event->revenue;

            $account     = BankAccount::find($revenue->account_id);
            $get_account = ChartOfAccount::find($account->chart_account_id);
            if(!empty($get_account))
            {
                $data = [
                    'account_id'         => !empty($get_account)? $get_account->id : 0,
                    'transaction_type'   => 'debit',
                    'transaction_amount' => $revenue->amount,
                    'reference'          => 'Revenue',
                    'reference_id'       => $revenue->id,
                    'reference_sub_id'   => 0,
                    'date'               => $revenue->date,
                ];
                AccountUtility::addTransactionLines($data , 'edit' , 'notes');
            }

            $account = ChartOfAccount::where('name','Accounts Receivable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'credit',
                'transaction_amount' => $revenue->amount,
                'reference'          => 'Revenue',
                'reference_id'       => $revenue->id,
                'reference_sub_id'   => 0,
                'date'               => $revenue->date,
            ];
            AccountUtility::addTransactionLines($data , 'edit');
        }
    }
}
