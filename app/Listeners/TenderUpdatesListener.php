<?php

namespace App\Listeners;

use App\Events\TenderUpdatesEvent;
use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

class TenderUpdatesListener
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
     * @param $tender
     * @return void
     */
    public function handle($tender)
    {
        $notification_service = new NotificationService();
        $tags = new Tags();
        $notification_service->create($tags, NotificationTemplate::TENDER_UPDATE, $tender->tender->organization->user->id, self::DEFAULT_LANGUAGE);
    }
}
