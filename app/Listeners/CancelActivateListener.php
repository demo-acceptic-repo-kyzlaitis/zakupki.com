<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\Cancel;
use App\Events\CancelActivateEvent;
use App\Jobs\SyncTender;
use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

class CancelActivateListener implements ShouldQueue
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
     * @param  CancelActivateEvent  $event
     * @return void
     */
    public function handle(CancelActivateEvent $event)
    {
        $cancel = $event->cancel;
        $api = new Api();
        $structure = new Cancel($cancel);

        if (!empty($cancel->cbd_id)) {
            $response = $api->patch($structure);
        } else {
            $response = $api->post($structure);
        }
        $job = (new SyncTender($cancel->cancelable->tender->cbd_id))->onQueue('tenders');
        $this->dispatch($job);


        $cancel->cbd_id = $response['data']['id'];
        $cancel->status = $response['data']['status'];
        $cancel->date = $response['data']['date'];
        $cancel->save();
    }
}
