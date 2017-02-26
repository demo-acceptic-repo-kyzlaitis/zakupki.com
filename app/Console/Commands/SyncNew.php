<?php

namespace App\Console\Commands;

use App\Api\Api;
use App\Jobs\SyncTender;
use App\Model\Tender;
use Carbon\Carbon;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;


class SyncNew extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync new changes with CDB';

    /**
     * Create a new command instance.
     *
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
        $apiUrl = env('PRZ_API_PUBLIC');
        $api = new Api(false, true);

//        $key = env('PRZ_KEY');

//        $cookieFile = storage_path('app/api_cookies').'/'.'tender_changes.txt';
        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => true
        ]);

        $this->info('Start');
        $response = $client->get($apiUrl.'/tenders?feed=changes&descending=1&mode=_all_');
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $this->info('Received data form start page');
            $data = json_decode($response->getBody(), true);
            if (isset($data['next_page']['offset'])) {
                while ($statusCode == 200) {
                    $offset = $data['next_page']['offset'];
                    $link = $apiUrl."/tenders?feed=changes&offset=$offset&mode=_all_";
                    $this->info("Get offset $offset");
                    $response = $client->get($link);
                    $data = json_decode($response->getBody(), true);
                    $statusCode = $response->getStatusCode();
                    if (isset($data['data']) && !empty($data['data'])) {
                        foreach ($data['data'] as $item) {
                            $itemData = $api->get($item['id']);
                            $queue = 'tenders';
                            $isNew = '';
                            $tenderModel = Tender::where('cbd_id', $item['id'])->first();
                            if (($tenderModel && $tenderModel->priority == 1) || (!$tenderModel)) {
                                $queue = 'tenders_high';
                            }
                            if (!$tenderModel) {
                                $isNew = 'new';
                            }
                            $job = (new SyncTender($item['id']))->onQueue($queue);
                            $this->dispatch($job);
                            $this->info($item['id']."\t".$item['dateModified']."\t".$itemData['data']['procurementMethod']."\t".$queue."\t".date('Y-m-d H:i:s')."\t".$isNew);
                        }
                    } elseif (isset($data['data']) && empty($data['data'])) {
                        $this->info('Sleep 30 seconds');
                        sleep(30);
                    }
                } //END OF WHILE LOOP
            }
        }
    }


    public function handle2()
    {
        $this->info('========= START ====================== ');
        $api = new Api(false, true);
        $api->updateMode = 'new';
        $api->getNextPageUri();
        while ($list = $api->getNext()) {
            foreach ($list['data'] as $value) {
                $this->info('----');
                $this->info($value['id']);
                $this->info($value['dateModified']);
                $this->info(date('Y-m-d H:i:s'));
                $tender = $api->get($value['id']);
                if ($tender['data']['procurementMethod'] == 'open') {
                    $job = (new SyncTender($value['id']))->onQueue('tenders');
                    $this->dispatch($job);
                } else {
                    $this->info("Skip {$tender['data']['procurementMethodType']}");
                }
            }
        }
    }

}
