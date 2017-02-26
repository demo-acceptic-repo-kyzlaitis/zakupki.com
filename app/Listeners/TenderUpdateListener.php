<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\TenderUpdate;
use App\Events\TenderUpdateEvent;
use App\Jobs\SyncTender;
use App\Model\Bid;
use App\Model\Lot;
use App\Model\Qualification;
use App\Model\TenderErrors;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Mockery\CountValidator\Exception;

class TenderUpdateListener implements ShouldQueue
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
     * @param  TenderUpdateEvent  $event
     * @return void
     */
    public function handle(TenderUpdateEvent $event)
    {
        $tender = $event->tender;
        $errors = TenderErrors::where('tender_id', $tender->id);
        $errors->delete();

        $api = new Api(false);
        $tenderStructure = new TenderUpdate($tender, ['status']);

        $response = $api->patch($tenderStructure);

        if ($api->responseCode == 200 || $api->responseCode == 201) {
            if (isset($response['data']['status'])) {
                $tender->status = $response['data']['status'];

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
            }
            $tender->save();

            $job = (new SyncTender($response['data']['id'], $tender))->onQueue('tenders');
            $this->dispatch($job);

        } elseif (isset($response['status']) && $response['status'] == 'error') {
            foreach ($response['errors'] as $error) {
                Mail::queue('emails.admin.publish-failed', ['id' => $tender->id, 'error' => $error, 'data' => $tenderStructure->getData()], function ($message) {
                    $message->to('azarov.andreas@gmail.com')->subject('Publish failed '.env('APP_ENV'));
                });
            }
        } else {
            throw new Exception();
        }


        /*Artisan::call('sync', [
            'id' => $tender->id
        ]);*/
    }
}
