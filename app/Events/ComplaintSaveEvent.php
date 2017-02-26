<?php

namespace App\Events;

use App\Events\Event;
use App\Model\Complaint;
use App\Model\Organization;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ComplaintSaveEvent extends Event
{
    use SerializesModels;

    public $complaint;
    public $organization;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Complaint $complaint, Organization $organization)
    {
        $this->complaint = $complaint;
        $this->organization = $organization;
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
