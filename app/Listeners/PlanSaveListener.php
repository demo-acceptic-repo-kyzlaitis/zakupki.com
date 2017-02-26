<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\Plan;
use App\Events\DocumentUploadEvent;
use App\Events\PlanDocumentUploadEvent;
use App\Events\PlanSaveEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Event;

class PlanSaveListener implements ShouldQueue
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
     * @param  PlanSaveEvent  $event
     * @return void
     */
    public function handle(PlanSaveEvent $event)
    {
        $plan = $event->plan;

        $api = new Api(false);
        $planStructure = new Plan($plan);

        if (!empty($plan->cbd_id)) {
            $response = $api->patch($planStructure);
        } else {
            $response = $api->post($planStructure);
        }
        if ($api->responseCode == 200 || $api->responseCode == 201) {

            foreach ($plan->documents as $document) {
                if ($document->url == '') {
                    Event::fire(new PlanDocumentUploadEvent($document));
                }
            }

            if (isset($response['access']['token'])) {
                $plan->access_token = $response['access']['token'];
                $plan->planID = $response['data']['planID'];
            }
            if (isset($response['data'])) {
                $plan->cbd_id = $response['data']['id'];
                $plan->planID = $response['data']['planID'];
            }
            $plan->save();
        }
    }
}
