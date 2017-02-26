<?php

namespace App\Console\Commands;

use App\Api\Api;
use App\Jobs\SyncContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;


class SyncContracts extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:contract {id=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $api = new Api(false, true);
        $api->namespace = 'contracts';
        $api->updateMode = 'new';
        $api->startListPage = '/contracts?feed=changes&descending=1&mode=_all_';


        $id = $this->argument('id');

        if (!empty($id)) {
            $time = microtime(true);
            $contract = $api->get($id);
            $this->info(microtime(true) - $time);
            $importer = new \App\Import\Contract($contract['data']);
            $importer->process();

            return;
        }

        $api->getNextPageUri();
        while ($list = $api->getNext()) {
            foreach ($list['data'] as $value) {
                $this->info($value['id']);
                $job = (new SyncContract($value['id']))->onQueue('tenders');
                $this->dispatch($job);
            }
        }


    }
}
