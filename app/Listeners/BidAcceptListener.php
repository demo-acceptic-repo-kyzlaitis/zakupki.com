<?php

namespace App\Listeners;

use App\Events\BidAcceptEvent;
use App\Model\Notification;
use App\Model\Tender;
use App\Model\Bid;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

class BidAcceptListener
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
     * @param  BidAcceptEvent  $event
     * @return void
     */
    public function handle(BidAcceptEvent $event)
    {

        $award = $event->bid;
        if($award->bid) {
             $bid = Bid::find($award->bid->id);
            $tender = Tender::find($award->tender_id);
            if ($bid->organization->user) {
                $notification_service = new NotificationService();
                $tags = new Tags();
                $tags->set_tender_link('<a href="'.URL::route('tender.show', [$tender->id]).'">'.$tender->tenderID.'</a>');
                $notification_service->create($tags, NotificationTemplate::OFFER_ACCEPT, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
            }
        }
    }
}
