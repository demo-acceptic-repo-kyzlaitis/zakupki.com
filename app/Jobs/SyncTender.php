<?php

namespace App\Jobs;

use App\Api\Api;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SyncTender extends Job implements ShouldQueue, SelfHandling
{

    public $id;
    public $tender;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $tender = null)
    {
        $this->id = $id;
        $this->tender = $tender;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = new Api(true, true);
        $id = $this->id;
        $tender = $api->get($id);
        $importer = new \App\Import\Tender($tender['data']);
        $importer->process($this->tender);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
    }
}
