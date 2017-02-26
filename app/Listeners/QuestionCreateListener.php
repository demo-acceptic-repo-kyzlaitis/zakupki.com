<?php

namespace App\Listeners;

use App\Api\Api;
use App\Api\Struct\Question;
use App\Events\QuestionCreateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuestionCreateListener implements ShouldQueue
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
     * @param  QuestionCreateEvent  $event
     * @return void
     */
    public function handle(QuestionCreateEvent $event)
    {
        $question = $event->question;
        $api = new Api();
        $response = $api->post(new Question($question));
        if (isset($response['data'])) {
//            Mail::queue('emails.admin.debug-data', ['response' => $question], function ($message) {
//                $message->to('illia.kyzlaitis.cv@gmail.com')->subject('Question data');
//            });
            $question->cbd_id = $response['data']['id'];
            $question->save();
        }
    }
}
