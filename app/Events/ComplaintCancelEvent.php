<?php

namespace App\Events;


use App\Model\Complaint;
use App\Model\User;
use Illuminate\Queue\SerializesModels;

class ComplaintCancelEvent extends Event
{
    use SerializesModels;


    public $complaint;
    public $tender;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Complaint $complaint, User $user, $tender)
    {
        $this->complaint = $complaint;
        $this->user = $user;
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
