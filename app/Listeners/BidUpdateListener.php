<?php

namespace App\Listeners;

use App\Events\BidUpdateEvent;
use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

class BidUpdateListener
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
     * @param  BidUpdateEvent  $event
     * @return void
     */
    public function handle(BidUpdateEvent $event)
    {
        $bid = $event->bid;
        if ($bid->organization && $bid->organization->user) {
            $notification_service = new NotificationService();
            $tags = new Tags();
            $tags->set_tender_link('<a href="'.URL::route('bid.list').'"> Мої пропозиції </a>');
            $notification_service->create($tags, NotificationTemplate::OFFER_UPDATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
        }
    }
}
