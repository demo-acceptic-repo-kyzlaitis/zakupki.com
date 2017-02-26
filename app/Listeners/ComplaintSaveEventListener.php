<?php

namespace App\Listeners;

use App\Api\Api;
use Event;
use App\Api\Struct\Complaint;
use App\Events\ComplaintSaveEvent;
use App\Events\ComplaintDocUploadEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class ComplaintSaveEventListener implements ShouldQueue
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
     * @param  ComplaintSaveEvent  $event
     * @return void
     */
    public function handle(ComplaintSaveEvent $event)
    {
        $complaint = $event->complaint;
        try {
            $api = new Api();
            $structure = new Complaint($complaint, $event->organization);

            if (!empty($complaint->cbd_id)) {
                $response = $api->patch($structure);
            } else {
                $response = $api->post($structure);
            }

            if (!empty($response['data']['id'])) {
                $complaint->cbd_id = $response['data']['id'];
            }
            if (!empty($response['access']['token'])) {
                $complaint->access_token = $response['access']['token'];
            }
            if (isset($response['data'])) {
                $complaint->type = $response['data']['type'];
                $complaint->status = $response['data']['status'];
                $complaint->date_submitted = isset($response['data']['dateSubmitted']) ? $response['data']['dateSubmitted'] : null;
                $complaint->date_answered = isset($response['data']['dateAnswered']) ? $response['data']['dateAnswered'] : null;
                $complaint->date_escalated = isset($response['data']['dateEscalated']) ? $response['data']['dateEscalated'] : null;

            }

            $complaint->save();

            foreach ($complaint->documents as $document) {
                if ($document->url == '') {
                    Event::fire(new ComplaintDocUploadEvent($document));
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
