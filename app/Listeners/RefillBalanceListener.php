<?php

namespace App\Listeners;

use App\Events\RefillBalanceEvent;
use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;


class RefillBalanceListener
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
     * @param  RefillBalnceEvent  $event
     * @return void
     */
    public function handle(RefillBalanceEvent $balance)
    {
        $notification_service = new NotificationService();
        $tags = new Tags();
        $tags->set_balance_link('<a href="'.URL::route('Payment.pay').'"> Мої пропозиції </a>');
        $notification_service->create($tags, NotificationTemplate::BALANCE_PLUS, $balance->user->id, self::DEFAULT_LANGUAGE);
    }
}
