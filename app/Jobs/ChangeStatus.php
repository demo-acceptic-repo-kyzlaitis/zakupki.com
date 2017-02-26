<?php

namespace App\Jobs;

use App\Api\Api;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ChangeStatus extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;
    public $entity;
    public $status;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($entity, $status)
    {
        $this->entity = $entity;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->entity->status = $this->status;
        $this->entity->save();
        $api = new Api();
        $structure = new \App\Api\Struct\ChangeStatus($this->entity);
        $api->patch($structure);

        if ($this->entity->type != 'qualification' || $this->status == 'cancelled')
            $this->dispatch((new SyncTender($structure->tender->cbd_id))->onQueue('tenders'));
    }
}
