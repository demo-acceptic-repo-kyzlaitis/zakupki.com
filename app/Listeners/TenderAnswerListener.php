<?php

namespace App\Listeners;

use App\Events\TenderAnswerEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenderAnswerListener implements ShouldQueue
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
     * @param  TenderAnswerEvent  $event
     * @return void
     */
    public function handle(TenderAnswerEvent $event)
    {
        $question = $event->question;
        try {
            $api = new \App\Api\Api();
            $struct = new \App\Api\Struct\Question($question);
            $data['data'] = $struct->getData();
            $api->patch($struct);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
