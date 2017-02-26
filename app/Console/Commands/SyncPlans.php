<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Api\Api;

class SyncPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncplans {id=0} {mode=new}';

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
        $id = $this->argument('id');
        $mode = $this->argument('mode');
        $api = new Api();
        $api->startListPage = '/plans?feed=changes&descending=1';
        $api->namespace = 'plans';

//        if (!empty($id)) {
//            $plan = $api->get($id);
//            $importer = new \App\Import\Tender($plan['data']);
//            $importer->process();
//            exit();
//        }

        while ($list = $api->getNext()) {
            foreach ($list['data'] as $value) {

                $this->info($value['id']);

//                $tenderModel = Tender::where('cbd_id', $value['id'])->first();
//                if ($mode == 'new' && $tenderModel && $tenderModel->date_modified == Carbon::parse($value['dateModified'])->format('Y-m-d H:i:s')) {
//                    $this->info("Exit on {$value['id']}");
//                    exit();
//                }

                $plan = $api->get($value['id']);

                try {
                    $importer = new \App\Import\Plan($plan['data']);
                    $importer->process();
                } catch (\Exception $e) {

                }
            }
            exit();
        }
    }
}
