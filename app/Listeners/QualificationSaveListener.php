<?php

namespace App\Listeners;

use App\Api\Struct\Qualification;
use App\Events\QualificationSaveEvent;
use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class QualificationSaveListener implements ShouldQueue
{
    protected $_logger;
    use DispatchesJobs;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_logger = new Logger('View Logs');
        $this->_logger->pushHandler(new StreamHandler('storage/logs/qualification.log', Logger::INFO));
    }

    /**
     * Handle the event.
     *
     * @param  QualificationSaveEvent $event
     * @return void
     */
    public function handle(QualificationSaveEvent $event)
    {
        $qualification = $event->qualification;
        $structure = new Qualification($qualification);
        $api = new \App\Api\Api(false);


        if (empty($qualification->cbd_id)) {
            $response = $api->post($structure);
        } else {
            $response = $api->patch($structure);

        }
        //$this->dispatch((new SyncTender($qualification->bid->tender->tender->cbd_id))->onQueue('tenders'));
        $this->_logger->addInfo($qualification->id . '; ' . json_encode($response));

        if ($api->responseCode == 200 || $api->responseCode == 201) {

            if (isset($response['data']['status'])) {
                $qualification->status = $response['data']['status'];
                $qualification->save();
            }
        } else {
            throw new Exception();
        }

    }
}
