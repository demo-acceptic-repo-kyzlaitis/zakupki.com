<?php

namespace App\Listeners;

use App\Events\ReturnMoneyEvent;
use App\Model\PaymentHistory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReturnMoneyListener
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
     * @param ReturnMoneyEvent $tender
     * @internal param ReturnMoneyEvent $event
     */
    public function handle(ReturnMoneyEvent $tender)
    {
        $tender = $tender->tender;
        $pending_operations = PaymentHistory::where('payment_services', '=', '3')->where('status_ps', '=', 'pending')->where('tender_id', '=', $tender->id)->get();
        foreach ($pending_operations as $operations){
            $operations->status_ps = 'returned';
            $operations->save();
            $operations->amount;
            $operations->user_id;
            $ub = UserBalance::find($operations->user_id);
            $ub->balance = $ub->balance + $operations->amount;
            $ub->save();
        }
    }
}
