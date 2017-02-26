<?php

namespace App\Listeners;

use App\Api\Struct\Bid;
use App\Events\BidDeleteEvent;
use App\Events\ReturnMoneyForInvalidBidEvent;
use App\Model\Notification;
use App\Payments\Payments;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Contracts\Queue\ShouldQueue;

class BidDeleteListener implements ShouldQueue
{
    const DEFAULT_LANGUAGE = 'ua';

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
     * @param  BidDeleteEvent  $event
     * @return void
     */
    public function handle(BidDeleteEvent $event)
    {
        $bid = $event->bid;
        if ($bid->organization && $bid->organization->user) {
            $notification_service = new NotificationService();
            $tags = new Tags();
            $notification_service->create($tags, NotificationTemplate::OFFER_DELETE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
        }
        $struct = new Bid($bid);
        $api = new \App\Api\Api(false);

        $api->delete($struct);
        if($bid->tender->mode == 1) {
            $this->_returnMoneyBack($bid);
        }
        $bid->delete();

//        if ($api->responseCode == 200) {
//            foreach ($bid->documents as $document) {
//                $document->delete();
//            }
//            $bid->delete();
//        }
    }

    private function _returnMoneyBack($bid) {
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
