<?php

namespace App\Console\Commands;

use App\Jobs\SyncBid;
use App\Jobs\SyncTender;
use App\Model\Tender;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SyncOur extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:our';

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
        $tenders = Tender::where('access_token', '!=', '')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'unsuccessful')
            ->where('status', '!=', 'complete')->get();

        foreach ($tenders as $tenderModel) {
            $this->info($tenderModel->cbd_id);
            $job = (new SyncTender($tenderModel->cbd_id))->onQueue('tenders');
            //$this->dispatch($job);

            $bids = $tenderModel->allBids()->where('access_token', '!=', '')->get();
            foreach ($bids as $bid) {
                $this->info('Dispatch:'.$bid->id);
                $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
            }
        }
    }
}
