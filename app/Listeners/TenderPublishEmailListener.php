<?php

namespace App\Listeners;

use App\Events\TenderPublishEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class TenderPublishEmailListener
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
     * @param  TenderPublishEvent  $event
     * @return void
     */
    public function handle(TenderPublishEvent $event)
    {
        $tender = $event->tender;
        $user = $tender->organization->user;
        Mail::queue('emails.admin.publish', ['tender' => $tender, 'user' => $user], function ($message) {
            $message->to('admin@zakupki.dev')->subject('Публикация нового тендера.');
        });
    }
}
