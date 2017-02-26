<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\AwardDocument;
use App\Jobs\ChangeStatus;
use App\Events\AwardSaveEvent;
use App\Events\AwardDocUploadEvent;
use App\Jobs\UploadDocument;
use App\Model\Award;
use Event;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

class AwardDocUploadSaveListener implements ShouldQueue
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
     * @param  AwardDocUploadEvent  $event
     * @return void
     */
    public function handle(AwardDocUploadEvent $event)
    {
        $document = $event->document;
        $api = new Api();
        $documentStruct = new AwardDocument($document);
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
			
            if ($document->format=='application/pkcs7-signature'){
            	$award = Award::find($document->award_id);
            	Event::fire(new AwardSaveEvent($award));
            	$status = $award->status=='activate'?'active':$award->status;
            	$status = $award->status=='unsuccessfully'?'unsuccessful':$status;
            	$this->dispatch(new ChangeStatus($award, $status));
            }
            
            $this->dispatch((new UploadDocument(new AwardDocument($document)))->onQueue('uploads'));

        }
    }
}
