<?php

namespace App\Jobs;

use App\Api\Api;
use App\Api\Struct\Contract;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ActivateContract extends Job implements ShouldQueue, SelfHandling
{
    public $contract;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($contract)
    {
        $this->contract = $contract;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->contract->status = 'active';
        $this->contract->save();

        $api = new Api(false);
        $structure = new Contract($this->contract);
        $api->patch($structure);
    }
}
