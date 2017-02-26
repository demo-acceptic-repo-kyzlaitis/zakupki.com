<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\CancelDocument;
use App\Events\CancelDocUploadEvent;
use App\Jobs\UploadDocument;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

class CancelDocUploadSaveListener implements ShouldQueue
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
     * @param  CancelDocUploadEvent  $event
     * @return void
     */
    public function handle(CancelDocUploadEvent $event)
    {
        $document = $event->document;
        $api = new Api();
        $documentStruct = new CancelDocument($document);
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
            $document->save();

            $this->dispatch((new UploadDocument(new CancelDocument($document)))->onQueue('uploads'));

        }
    }
}
