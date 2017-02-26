<?php

namespace App\Jobs;

use App\Api\Api;
use App\Api\Struct\BidDocument;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DeleteBidDocument extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;
    public $entity;
    public $status;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $struct = new BidDocument($this->entity);
        $api = new Api(false);

        $api->delete($struct);
    }
}
