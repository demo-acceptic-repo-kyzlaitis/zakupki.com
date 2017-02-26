<?php

namespace App\Listeners;

use App\Api\Struct\Bid;
use App\Events\BidDocUploadEvent;
use App\Events\BidSaveEvent;
use Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class BidSaveListener implements ShouldQueue
{
    protected $_logger;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_logger = new Logger('View Logs');
        $this->_logger->pushHandler(new StreamHandler(storage_path('/logs/bids.log'), Logger::INFO));
    }

    /**
     * Handle the event.
     *
     * @param  BidSaveEvent  $event
     * @return void
     */
    public function handle(BidSaveEvent $event)
    {
        $bid = $event->bid;
        $structure = new Bid($bid);
        if ($bid->tender && ($bid->tender->status == 'active.qualification' || $bid->tender->status == 'active.awarded')) {
            foreach ($bid->documents as $document) {
                if (empty($document->url)) {
                    Event::fire(new BidDocUploadEvent($document));
                }
            }
        } else {
            $api = new \App\Api\Api(false);

            if (empty($bid->cbd_id)) {
                $response = $api->post($structure);
            } else {
                $response = $api->patch($structure);

            }
            $this->_logger->addInfo($bid->id.'; '.json_encode($response));

            foreach ($bid->documents as $document) {
                if (empty($document->url)) {
                    Event::fire(new BidDocUploadEvent($document));
                }
            }

            if ($api->responseCode == 200 || $api->responseCode == 201 || $api->responseCode == 403) {

                if ($api->responseCode == 201) {
                    Mail::queue('emails.admin.publish-failed', ['id' => $bid->tender->id, 'data' => $response], function ($message) {
                        $message->to('spasibova@zakupki.com.ua')->subject('Предложение опубликовано успешно ('.env('APP_ENV').') ');
                    });
                }
                if (isset($response['data']['id'])) {
                    $bid->cbd_id = $response['data']['id'];
                    $bid->status = $response['data']['status'];
                    if (isset($response['access']['token'])) {
                        $bid->access_token = $response['access']['token'];
                    }
                    $bid->save();
                }
            } elseif (isset($response['status']) && $response['status'] == 'error') {
                foreach ($response['errors'] as $error) {
                    Mail::queue('emails.admin.publish-failed', ['time' => $event->time, 'user' => $event->user->id, 'id' => $bid->id, 'error' => $error, 'data' => $structure->getData()], function ($message) {
                        $message->to('spasibova@zakupki.com.ua')->subject('Ошибка публикации предложения ('.env('APP_ENV').') ');
                    });
                }
            } else {
                throw new \Exception();
            }
        }
    }
}
