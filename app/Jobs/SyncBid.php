<?php

namespace App\Jobs;

use App\Api\Api;
use App\Model\Bid;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncBid extends Job implements ShouldQueue, SelfHandling
{
    public $id;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bid = Bid::find($this->id);
        $api = new Api(false);
        $response = $api->get($bid->bidable->tender->cbd_id, 'bids/'.$bid->cbd_id, $bid->access_token);
        if (isset($response['data'])) {
            if (isset($response['data']['participationUrl'])) {
                $bid->participation_url = $response['data']['participationUrl'];
            } elseif (isset($response['data']['lotValues'][0]['participationUrl'])) {
                $bid->participation_url = $response['data']['lotValues'][0]['participationUrl'];
            }
            if (isset($response['data']['status'])) {
                $bid->status = $response['data']['status'];
            }
            $bid->save();
        }
    }
}
