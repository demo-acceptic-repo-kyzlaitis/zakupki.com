<?php

namespace App\Listeners;

use Event;
use App\Api\Api;
use App\Api\Struct\Cancel;
use App\Events\CancelDocUploadEvent;
use App\Events\CancelSaveEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CancelSaveListener implements ShouldQueue
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
     * @param  CancelSaveEvent  $event
     * @return void
     */
    public function handle(CancelSaveEvent $event)
    {
        try {
            $cancel = $event->cancel;
            $api = new Api();
            $structure = new Cancel($cancel);

            if (!empty($cancel->cbd_id)) {
                $response = $api->patch($structure);
            } else {
                $response = $api->post($structure);
            }

            if (!empty($response['data']['id'])) {
                $cancel->cbd_id = $response['data']['id'];
                $cancel->save();
            }
            foreach ($cancel->documents as $document) {
                Event::fire(new CancelDocUploadEvent($document));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
