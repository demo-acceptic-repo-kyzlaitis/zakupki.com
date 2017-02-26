<?php

namespace App\Events;

use App\Events\Event;
use App\Model\Cancellation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CancelSaveEvent extends Event
{
    use SerializesModels;

    public $cancel;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Cancellation $cancel)
    {
        $this->cancel = $cancel;
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
