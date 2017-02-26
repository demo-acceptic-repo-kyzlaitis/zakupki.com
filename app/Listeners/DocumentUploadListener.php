<?php

namespace App\Listeners;

use App\Events\DocumentUploadEvent;
use App\Jobs\UploadDocument;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Api\Api;
use App\Api\Struct\Document;
use Carbon\Carbon;

class DocumentUploadListener implements ShouldQueue
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
     * @param  DocumentUploadEvent  $event
     * @return void
     */
    public function handle(DocumentUploadEvent $event)
    {
        $document = $event->document;
        $documentStructure = new Document($document);
        $hash = md5(file_get_contents($documentStructure->getFullPath()));

        $api = new Api();
        $response = $api->registerDoc($hash);

        if ($api->responseCode == 201) {
            $document->upload_url = $response['upload_url'];
            $document->url = $response['data']['url'];
            $document->hash = $hash;
            $document->save();

            $response = $api->post($documentStructure);
            $document->date_published = Carbon::parse($response['data']['datePublished'])->format('Y-m-d H:i:s');
            $document->date_modified = Carbon::parse($response['data']['dateModified'])->format('Y-m-d H:i:s');
            $document->format = $response['data']['format'];
            $document->orig_id = $response['data']['id'];
            $document->title = $response['data']['title'];
            $document->url = $response['data']['url'];
            $document->save();

            $this->dispatch((new UploadDocument(new Document($document)))->onQueue('uploads'));

        }

    }
}
