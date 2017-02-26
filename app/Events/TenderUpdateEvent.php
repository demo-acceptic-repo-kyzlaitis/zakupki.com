<?php

namespace App\Events;

use App\Model\Tender;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TenderUpdateEvent extends Event
{
    use SerializesModels;

    public $tender;

    /**
     * TenderUpdateEvent constructor.
     *
     * @param Tender $tender
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
