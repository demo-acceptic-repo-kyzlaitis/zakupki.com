<?php

namespace App\Jobs;

use App\Api\Api;
use App\Model\Tender;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GetTenderCredentials extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;
    public $id;
    public $tender;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->tender = Tender::where('cbd_id', $id)->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = new Api();
        $url = '/tenders/'.$this->id.'/credentials?acc_token='.$this->tender->stages->firstStage->access_token;
        $response = $api->patchRaw($url, []);
        if ($api->responseCode == 200 || $api->responseCode == 201) {

            if (isset($response['access']['token'])) {
                $this->tender->access_token = $response['access']['token'];
                $this->tender->save();

                if ($this->tender->status == 'draft.stage2') {
                    $this->dispatch((new ChangeStatus($this->tender, 'active.tendering')));
                }
            }

        }
    }
}
