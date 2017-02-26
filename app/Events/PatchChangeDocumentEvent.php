<?php
/**
 * Created by PhpStorm.
 * User: illia
 * Date: 8/31/2016
 * Time: 9:47 PM
 */

namespace App\Events;


use Illuminate\Queue\SerializesModels;

class PatchChangeDocumentEvent extends Event
{

    use SerializesModels;

    public $contract;
    public $document;
    public $relatedItem;


    /**
     * Create a new event instance.
     *
     * @param $contract
     * @param $document
     * @param $relatedItem
     */
    public function __construct($contract, $document, $relatedItem)
    {
        $this->document = $document;
        $this->contract = $contract;
        $this->relatedItem = $relatedItem;
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