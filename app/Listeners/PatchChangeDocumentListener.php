<?php
/**
 * Created by PhpStorm.
 * User: illia
 * Date: 8/31/2016
 * Time: 9:50 PM
 */

namespace App\Listeners;


use App\Events\PatchChangeDocumentEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class PatchChangeDocumentListener implements ShouldQueue
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
     * @param  PlanDocumentUploadEvent $event
     * @return void
     */
    public function handle(PatchChangeDocumentEvent $event) {
        $api = new Api();
        $rawUrl = '/contracts/'.$event->contract->cbd_id.'/documents/'.$event->document->cbd_id;
        $response = $api->patchRaw($rawUrl, ['data' =>
                                     [
                                         'documentOf' => 'change',
                                         'relatedItem' => $event->relatedItem->cbd_id
                                     ]
                                ]);
        if($response->responseCode == 200 || $response->responseCode == 201) {

        } else {
            throw  new \Exception();
        }
    }
}