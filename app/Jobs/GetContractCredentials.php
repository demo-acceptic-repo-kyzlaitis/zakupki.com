<?php

namespace App\Jobs;

use App\Api\Api;
use App\Jobs\Job;
use App\Model\Contract;
use App\Model\Tender;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class GetContractCredentials extends Job implements SelfHandling, ShouldQueue
{
    public $id;
    public $contract;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->contract = Contract::where('cbd_id', $id)->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = new Api();
        $url = '/contracts/'.$this->id.'/credentials?acc_token='.$this->contract->tender->access_token;
        $response = $response = $api->patchRaw($url, []);

        if ($api->responseCode == 200 || $api->responseCode == 201) {

            if (isset($response['access']['token'])) {
                $this->contract->access_token = $response['access']['token'];
                $this->contract->save();
            }
        }
    }
}
