<?php

namespace App\Listeners;

use Event;
use App\Api\Api;
use App\Api\Struct\Lot;
use App\Api\Struct\Tender;
use App\Events\DocumentUploadEvent;
use App\Events\TenderSaveEvent;
use App\Model\TenderErrors;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use GuzzleHttp\Exception\BadResponseException;
use Mockery\CountValidator\Exception;

class TenderSaveListenerSync
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
     * @param  TenderSaveEvent  $event
     * @return void
     */
    public function handle(TenderSaveEvent $event)
    {
        $tender = $event->tender;
        $errors = TenderErrors::where('tender_id', $tender->id);
        $errors->delete();

        $api = new Api(false);
        $tenderStructure = new Tender($tender);

        if (!empty($tender->cbd_id)) {
            $response = $api->patch($tenderStructure);
        } else {
            $response = $api->post($tenderStructure);
        }
        if ($api->responseCode == 200 || $api->responseCode == 201) {

            if (isset($response['access']['token'])) {
                $tender->access_token = $response['access']['token'];
            }
            if ($tender->published_at == '0000-00-00 00:00:00') {
                $tender->published_at = date('Y-m-d H:i:s');
            }
            $tender->save();

            $importer = new \App\Import\Tender($response['data']);
            $importer->process($tender);
            foreach ($tender->documents as $document) {
                if ($document->url == '') {
                    Event::fire(new DocumentUploadEvent($document));
                }
            }
            foreach ($tender->lots as $lot) {
                foreach ($lot->documents as $document) {
                    if ($document->url == '') {
                        Event::fire(new DocumentUploadEvent($document));
                    }
                }
                foreach ($lot->items as $item) {
                    foreach ($item->documents as $document) {
                        if ($document->url == '') {
                            Event::fire(new DocumentUploadEvent($document));
                        }
                    }
                }
            }


        } elseif (isset($response['status']) && $response['status'] == 'error') {
            foreach ($response['errors'] as $error) {
                $errorModel = TenderErrors::where('tender_id', $tender->id)->where('hash', md5(json_encode($error)))->first();
                if (!$errorModel) {
                    $errorModel = new TenderErrors([
                        'tender_id' => $tender->id,
                        'hash' => md5(json_encode($error)),
                        'message' => json_encode($error)
                    ]);
                    $errorModel->save();
                }
            }
        } else {
            throw new Exception();
        }
    }
}
