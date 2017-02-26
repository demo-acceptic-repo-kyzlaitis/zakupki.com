<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\Contract;
use App\Api\Struct\ContractChange;
use App\Events\ContractDocUploadEvent;
use App\Events\ContractSaveEvent;
use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class ContractSaveListener implements ShouldQueue
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
     * @param  ContractSaveEvent  $event
     * @return void
     */
    public function handle(ContractSaveEvent $event)
    {
        $contract = $event->contract;
        $structure = new Contract($contract);
        $api = new Api(false);

        if (empty($contract->cbd_id)) {
            $api->post($structure);
        } else {
            $api->patch($structure);
        }

        foreach ($contract->documents as $document) {
            if (empty($document->url)) {
                Event::fire(new ContractDocUploadEvent($document));
            }
        }

        if ($contract->change) {
            $changeStructure = new ContractChange($contract->change);
            if (empty($contract->change->cbd_id)) {
                $response = $api->post($changeStructure);
            } else {
                $response = $api->patch($changeStructure);
            }
            if ($api->responseCode == 200 || $api->responseCode == 201) {
                if (!empty($response['data']['id'])) {
                    $contract->change->cbd_id = $response['data']['id'];
                }
                $contract->change->date = $response['data']['date'];
                $contract->change->save();
            }
        }
    }
}
