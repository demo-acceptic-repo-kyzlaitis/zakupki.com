<?php

namespace App\Events;

use App\Events\Event;
use App\Model\Tender;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TenderPublishEvent extends Event
{
    use SerializesModels;

    public $tender;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tender $tender)
    {
        $this->tender = $tender;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
