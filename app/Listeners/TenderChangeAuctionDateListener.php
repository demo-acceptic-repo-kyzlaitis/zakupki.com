<?php

namespace App\Listeners;

use App\Api\Api;
use App\Events\TenderChangeAuctionDateEvent;
use App\Events\TenderChangeStatusEvent;
use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\URL;

class TenderChangeAuctionDateListener implements ShouldQueue
{

    const DEFAULT_LANGUAGE = 'ua';

    protected $_api;

    protected function _processBid($bid)
    {
        $response = $this->_api->get($bid->bidable->tender->cbd_id, 'bids/'.$bid->cbd_id, $bid->access_token);
        if (isset($response['data'])) {
            if (isset($response['data']['participationUrl'])) {
                $bid->participation_url = $response['data']['participationUrl'];
            } elseif (isset($response['data']['lotValues'][0]['participationUrl'])) {
                $bid->participation_url = $response['data']['lotValues'][0]['participationUrl'];
            }
            $bid->save();
        }
    }

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_api = new Api();
    }

    /**
     * Handle the event.
     *
     * @param  TenderChangeStatusEvent  $event
     * @return void
     */
    public function handle(TenderChangeAuctionDateEvent $event)
    {
        $tender = $event->tender;
        foreach ($tender->bids as $bid) {
            $this->_processBid($bid);
            if ($bid->organization->user) {
                $notification_service = new NotificationService();
                $tags = new Tags();
                $tags->set_tender_name($tender->tenderID);
                $tags->set_tender_link('<a href="'.URL::route('tender.show', [$tender->id]).'">'.$tender->tenderID.'</a>');
                $tags->set_tender_date($tender->auction_start_date);
                $notification_service->create($tags, NotificationTemplate::TENDER_SET_DATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
            }
        }
    }
}
