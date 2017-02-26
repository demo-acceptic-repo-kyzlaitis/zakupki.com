<?php

namespace App\Events;

use App\Events\Event;
use App\Model\Bid;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BidDeleteEvent extends Event
{
    use SerializesModels;

    public $bid;

    /**
     * Create a new event instance.
     *
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
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
