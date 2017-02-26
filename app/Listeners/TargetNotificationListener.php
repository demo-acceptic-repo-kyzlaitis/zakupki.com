<?php

namespace App\Listeners;

use App\Events\TargetNotificationEvent;
use App\Model\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TargetNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TargetNotificationEvent $event
     * @return void
     */
    public function handle(TargetNotificationEvent $event) {
        $organizations = $event->query->get();

        foreach ($organizations as $organization) {
            Notification::create([
                'user_id'               => $organization->user->id,
                'title'                 => $event->data['title'],
                'text'                  => $event->data['text'],
                'alias'                 => 'custom.notification',
                'notificationable_id'   => $organization->id,
                'notificationable_type' => 'App\Model\Organization',
            ]);
        }
    }
}
