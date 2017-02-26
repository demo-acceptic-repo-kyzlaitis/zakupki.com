<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RefillBalanceEvent extends Event
{
    use SerializesModels;

    public $balance;
    public $sum;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user,$sum)
    {
        $this->user = $user;
        $this->sum = $sum;
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
