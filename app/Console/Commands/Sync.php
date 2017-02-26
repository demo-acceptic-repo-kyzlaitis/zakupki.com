<?php

namespace App\Console\Commands;

use App\Api\Api;
use App\Jobs\SyncTender;
use App\Model\Tender;
use Carbon\Carbon;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Mockery\CountValidator\Exception;


class Sync extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync {id=0} {mode=new}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync with CDB';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle2()
    {
        $apiUrl = env('PRZ_API');

        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => true
        ]);

        $this->info('Start');
        $response = $client->get($apiUrl.'/tenders');
        $statusCode = $response->getStatusCode();
        $this->info('Received data form start page');
        $data = json_decode($response->getBody(), true);
        $i = 0;
        while ($statusCode == 200) {
            if (isset($data['data']) && !empty($data['data'])) {
                foreach ($data['data'] as $item) {
                    $tender = Tender::where('cbd_id', $item['id'])->first();
                    if (!$tender || strtotime($tender->date_modified) != strtotime($item['dateModified'])) {
                        $i++;

                        $job = (new SyncTender($item['id']))->onQueue('tenders_old');
                        $this->dispatch($job);
                        $this->info($i."\t".$item['id']."\t".$item['dateModified']."\t".date('Y-m-d H:i:s'));
                    }
                }
            }
            $offset = $data['next_page']['uri'];
            $this->info("Get offset $offset");
            file_put_contents('offset', $offset);
            $response = $client->get($offset);
            $data = json_decode($response->getBody(), true);
            $statusCode = $response->getStatusCode();
        }
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $api = new Api(false);

        $id = $this->argument('id');
        $mode = $this->argument('mode');

        if (!empty($id)) {
            $time = microtime(true);
            $tender = $api->get($id);
            $this->info(microtime(true) - $time);
            $importer = new \App\Import\Tender($tender['data']);
            $importer->process();

            return;
        }

        $i = 0;
        $controlDate = mktime(0,0,0,date('m'), date('d') - 1, date('Y'));
        $this->info(date('d.m.Y H:i:s', $controlDate));
//        sleep(5);

        while ($list = $api->getNext()) {
//            sleep(1);

            $file = fopen('storage/logs/SyncAll-' . date('Y-m-d') . '.log', 'a');
            fputs($file, '['.date('Y-m-d H:i:s').'] [' . $list['current_uri'] . '] [' . $api->getNextPage() . '] ['.json_encode($list)."]\r\n");
            fclose($file);

            print "Count: ".count($list['data'])."\n";
            $j = 0;
            foreach ($list['data'] as $value) {
                $date = strtotime($value['dateModified']);
                if ($date < $controlDate) {
                    continue;
                }
                $tender = Tender::where('cbd_id', $value['id'])->first();
                $j++;
                if (!$tender || strtotime($tender->date_modified) != strtotime($value['dateModified'])) {
                    $i++;
                    $this->info($value['id']);
                    $this->dispatch((new SyncTender($value['id']))->onQueue('tenders_high'));
                }
            }
            if ($j == 0) {
                $this->info($i);
                break;
            }
        }
    }
}
