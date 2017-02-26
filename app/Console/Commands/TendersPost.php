<?php

namespace App\Console\Commands;

use App\Api\Api;
use Illuminate\Console\Command;


use App\Model\Tender,
    App\Model\Organization,
    App\Model\Currencies,
    App\Model\Bid,
    App\Model\Award;

class TendersPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenders:post {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $tender = Tender::find($this->argument('id'));

        $api = new \App\Api\Api(false);
        $tenderStruct = new \App\Api\Struct\Tender($tender);
        $data['data'] = $tenderStruct->getData();


        $this->info(var_export($data, true));

        if (!empty($tender->cbd_id)) {
            $response = $api->patch($tenderStruct);
        } else {
            $response = $api->post($tenderStruct);
        }
        var_dump($response);
        if ((isset($response['status']) && $response['status'] == 'success') || isset($response['access']['token'])) {

            if (isset($response['access']['token'])) {
                $tender->access_token = $response['access']['token'];
            }
            $tender->tenderID = $response['data']['tenderID'];
            $tender->cbd_id = $response['data']['id'];
            $tender->status = $response['data']['status'];
            if ($tender->published_at == '0000-00-00 00:00:00') {
                $tender->published_at = date('Y-m-d H:i:s');
            }
            $tender->save();
        }
    }
}
