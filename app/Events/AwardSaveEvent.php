<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AwardSaveEvent extends Event
{
    use SerializesModels;
    public $award;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($award)
    {
        $this->award = $award;
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
