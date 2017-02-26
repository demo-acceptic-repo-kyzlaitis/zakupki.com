<?php

namespace App\Listeners;

use App\Events\ReturnMoneyForInvalidBidEvent;
use App\Payments\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReturnMoneyForInvalidBidListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ReturnMoneyForInvalidBidEvent $event
     * @return void
     */
    public function handle(ReturnMoneyForInvalidBidEvent $event) {
        $bid  = $event->bid;
        $user = $bid->organization->user;

        /**
         * 3 - id в таблице payment_services (значение billing)
         * billing - виртульаные средства
         */
        $payment_service = 3;

        $balance         = $user->balance;

        $returnAmount = Payments::getPrice($bid->bidable->amount);// сума которая возврщается юзеру

        /**
         * 3 - это айди в некой таблице products в которой хранятся "цель платежа"
         * например 2 - это "Ручной ввод"
         *          1 - это "Подача предложений"
         */
        $balance->plus($returnAmount, $payment_service, ['comment' => 'invalid пропозиція'], 3);
    }
}
