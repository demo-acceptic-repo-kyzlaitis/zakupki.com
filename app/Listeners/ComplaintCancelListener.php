<?php

namespace App\Listeners;

use App\Events\ComplaintCancelEvent;
use Illuminate\Support\Facades\Mail;

class ComplaintCancelListener
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
     * @param  ComplaintCancelEvent  $event
     * @return void
     */
    public function handle(ComplaintCancelEvent $event)
    {
        $user = $event->user;
        $tender = $event->complaint->tender()->first();


        Mail::queue('emails.complaint.cancelled',
            ['user' => $user, 'complaint' => $event->complaint, 'tender' => $tender],
            function ($message) use ($event, $user) {
            $message->to($user->email)->subject('Відкликання оскарження №' . $event->complaint->id);
        });
    }
}
