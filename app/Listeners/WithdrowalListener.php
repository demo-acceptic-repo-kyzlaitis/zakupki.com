<?php

namespace App\Listeners;

use App\Events\WithdrowalEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Model\PaymentHistory;
class WithdrowalListener
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
     * @param  WithdrowalEvent  $event
     * @return void
     */
    public function handle(WithdrowalEvent $tender)
    {
        $tender = $tender->tender;
        $pending_operations = PaymentHistory::where('payment_services', '=', '3')->where('status_ps', '=', 'pending')->where('tender_id', '=', $tender->id)->get();
        foreach ($pending_operations as $operations){
            $operations->status_ps = 'completed';
            $operations->save();
        }
    }
}
