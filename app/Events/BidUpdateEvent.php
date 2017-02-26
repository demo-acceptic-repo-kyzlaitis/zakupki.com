<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BidUpdateEvent extends Event
{
    use SerializesModels;
    public $bid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($bid)
    {
        $this->bid = $bid;
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
