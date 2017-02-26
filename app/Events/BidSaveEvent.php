<?php

namespace App\Events;

use App\Events\Event;
use App\Model\Bid;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;

class BidSaveEvent extends Event
{
    use SerializesModels;

    public $bid;
    public $time;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
        $this->time = date('Y-m-d H:i:s');
        $this->user = Auth::user();
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
