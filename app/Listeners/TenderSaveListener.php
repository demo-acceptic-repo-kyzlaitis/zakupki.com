<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\Lot;
use App\Api\Struct\Tender;
use App\Events\DocumentUploadEvent;
use App\Events\TenderSaveEvent;
use App\Jobs\SyncTender;
use App\Model\Bid;
use App\Model\Qualification;
use App\Model\TenderErrors;
use Bugsnag\BugsnagLaravel\BugsnagFacade;
use Event;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Mockery\CountValidator\Exception;

class TenderSaveListener implements ShouldQueue
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
     * @param  TenderSaveEvent  $event
     * @return void
     */
    public function handle(TenderSaveEvent $event)
    {

        $tender = $event->tender;
        $errors = TenderErrors::where('tender_id', $tender->id);
        $errors->delete();

        $api = new Api();
        $tenderStructure = new Tender($tender);

        if (!empty($tender->cbd_id)) {
            $response = $api->patch($tenderStructure);
            $isNew = false;
            //Lot needs additional patch
            if ($tender->procedureType->procurement_method != 'selective') {
                foreach ($tender->lots as $lot) {
                    $lotStructure = new Lot($lot);
                    if ($lot->cbd_id != '') {
                        $api->patch($lotStructure);
                    } else {
                        $api->post($lotStructure);
                    }
                }
            }
        } else {
            $isNew = true;
            $response = $api->post($tenderStructure);
        }
//        if ($api->responseCode !== 200 ) {
//            Mail::queue('emails.admin.publish-new', ['id' => $tender->id], function ($message, $api) {
//                $message->to('illia.kyzlaitis.cv@gmail.com')->subject($api->responseCode);
//            });
//        }
        if ($api->responseCode == 200 || $api->responseCode == 201) {

            if (isset($response['access']['token'])) {
                $tender->access_token = $response['access']['token'];
            }
            if (isset($response['data']['id'])) {
                $tender->cbd_id = $response['data']['id'];
            }
            if ($tender->published_at == '0000-00-00 00:00:00') {
                $tender->published_at = date('Y-m-d H:i:s');
            }
            if (!empty($response['data']['qualifications'])) {
                foreach ($response['data']['qualifications'] as $qualify) {
                    $qualification = Qualification::where('cbd_id', $qualify['id'])->first();
                    if ($qualification == null)
                        $qualification = new Qualification();

                    $qualification->status = $qualify['status'];
                    $qualification->eligible = $qualify['eligible'];
                    $qualification->qualified = $qualify['qualified'];
                    $qualification->cbd_id = $qualify['id'];
                    $qualification->bid_id = Bid::where('cbd_id', $qualify['bidID'])->pluck('id');
                    $qualification->lot_id = Lot::where('cbd_id', $qualify['lotID'])->pluck('id');
                    $qualification->save();
                }
            }
            $tender->save();

            $job = (new SyncTender($response['data']['id'], $tender))->onQueue('tenders_high');
            $this->dispatch($job);

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

            if ($isNew && !isset($response['mode'])) {
                Mail::queue('emails.admin.publish-new', ['id' => $tender->id], function ($message) {
                    $message->to('spasibova@zakupki.com.ua')->subject('New tender '.env('APP_ENV'));
                    $message->to('manager@zakupki.com.ua')->subject('New tender '.env('APP_ENV'));
                });
            }
        } elseif (isset($response['status']) && $response['status'] == 'error') {
            foreach ($response['errors'] as $error) {
                Mail::queue('emails.admin.publish-failed', ['id' => $tender->id, 'error' => $error, 'data' => $tenderStructure->getData()], function ($message) {
                    $message->to('azarov.andreas@gmail.com')->subject('Publish failed '.env('APP_ENV'));
                    $message->to('illia.kyzlaitis.cv@gmail.com')->subject('Publish failed '.env('APP_ENV'));
                });
            }
        } else {
            throw new Exception();
        }
    }
}
