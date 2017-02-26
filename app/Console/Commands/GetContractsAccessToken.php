<?php

namespace App\Console\Commands;

use Event;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class GetContractsAccessToken extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncContractsAccess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get contract access_toke for old contracts';

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
    public function handle() {
        $results = DB::table('contracts')
            ->join('tenders', 'contracts.tender_id', '=', 'tenders.id')
            ->select('contracts.cbd_id')
            ->where('contracts.access_token', 'like', '') // у которых
            ->where('contracts.cbd_id', 'not like', '') // которые уже получили цбд
            ->where('tenders.source', '=', 0)->get(); // все контраты наших тенедеров

        foreach($results as $result) {
            $this->dispatch(new \App\Jobs\GetContractCredentials($result->cbd_id));
        }
    }
}
