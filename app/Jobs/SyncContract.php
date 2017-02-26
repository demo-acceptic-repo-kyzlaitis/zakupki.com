<?php

namespace App\Jobs;

use App\Api\Api;
use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;


class SyncContract extends Job implements SelfHandling, ShouldQueue
{

    public $id;
    public $contract;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $contract = '')
    {
        $this->id = $id;
        $this->contract = $contract;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = new Api(false, true);
        $api->namespace = 'contracts';
        $api->updateMode = 'new';

        $contract = $api->get($this->id);
        $importer = new \App\Import\Contract($contract['data']);
        $importer->process();

    }
}
