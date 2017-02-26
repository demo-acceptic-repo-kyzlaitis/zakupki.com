<?php

namespace App\Jobs;

use App\Api\Api;
use App\Api\Struct\Award;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Mail;

class ActivateAward extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;
    public $award;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($award)
    {
        $this->award = $award;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->award->status == 'activate') {
            $this->award->status = 'active';
            $this->award->save();
            $api = new Api();
            $structure = new Award($this->award);
            $api->patch($structure);
            $this->dispatch((new SyncTender($this->award->tender->cbd_id))->onQueue('tenders'));
        }
    }
}
