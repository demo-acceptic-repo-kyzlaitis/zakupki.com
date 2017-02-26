<?php

namespace App\Events;

use App\Model\Qualification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class QualificationSaveEvent extends Event
{
    use SerializesModels;

    public $qualification;
    public $time;
    public $user;

    /**
     * QualificationSaveEvent constructor.
     *
     * @param Qualification $qualification
     */
    public function __construct(Qualification $qualification)
    {
        $this->qualification = $qualification;
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
