<?php

/**
 * Author @illia 03.01.2017
 */

namespace App\Events;

use App\Events\Event;
use App\Model\Bid;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReturnMoneyForInvalidBidEvent extends Event
{
    use SerializesModels;


    public $bid;

    /**
     * Create a new event instance.
     *
     * @param Bid $bid model
     */
    public function __construct(Bid $bid) {
        $this->bid = $bid;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn() {
        return [];
    }
}
