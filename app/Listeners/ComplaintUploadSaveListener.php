<?php

namespace App\Listeners;

use App\Api\Api;
use App\Events\ComplaintDocUploadEvent;
use App\Api\Struct\ComplaintDocument;
use App\Jobs\UploadDocument;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ComplaintUploadSaveListener
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
     * @param  ComplaintDocUploadEvent  $event
     * @return void
     */
    public function handle(ComplaintDocUploadEvent $event)
    {
        $document = $event->document;
        $api = new Api();
        $documentStruct = new ComplaintDocument($document);
        $hash = md5(file_get_contents($documentStruct->getFullPath()));

        $response = $api->registerDoc($hash);

        if ($api->responseCode == 201) {
            $document->upload_url = $response['upload_url'];
            $document->url = $response['data']['url'];
            $document->hash = $hash;
            $document->save();

            $response = $api->post($documentStruct);
            $document->date_published = Carbon::parse($response['data']['datePublished'])->format('Y-m-d H:i:s');
            $document->date_modified = Carbon::parse($response['data']['dateModified'])->format('Y-m-d H:i:s');
            $document->format = $response['data']['format'];
            $document->orig_id = $response['data']['id'];
            $document->title = $response['data']['title'];
            $document->url = $response['data']['url'];
            if (isset($response['data']['author'])) {
                $document->author = $response['data']['author'];
            }

            $document->save();

            $this->dispatch((new UploadDocument(new ComplaintDocument($document)))->onQueue('uploads'));

        }
    }
}
