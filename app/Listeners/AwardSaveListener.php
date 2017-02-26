<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\Award;
use App\Events\AwardDocUploadEvent;
use App\Events\AwardSaveEvent;
use App\Jobs\SyncTender;
use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class AwardSaveListener implements ShouldQueue
{
    use DispatchesJobs;
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
     * @param  AwardSaveEvent  $event
     * @return void
     */
    public function handle(AwardSaveEvent $event)
    {
        $award = $event->award;

//        if ($award->status == 'active') {
//            return;
//        }

        $api = new Api();
        $structure = new Award($award);
        if (empty($award->cbd_id)) {
            $response = $api->post($structure);
        } else {
            $response = $api->patch($structure);
        }
        if (!empty($response['data']['id'])) {
            $award->cbd_id = $response['data']['id'];
            $award->save();
        }

        foreach ($award->documents as $document) {
            if ($document->url == '') {
                Event::fire(new AwardDocUploadEvent($document));
            }
        }
    }
}
