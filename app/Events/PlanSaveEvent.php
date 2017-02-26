<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PlanSaveEvent extends Event
{
    use SerializesModels;

    public $plan;

    /**
     * Create a new event instance.
     *
     * @param $plan
     */
    public function __construct($plan)
    {
        $this->plan = $plan;
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
