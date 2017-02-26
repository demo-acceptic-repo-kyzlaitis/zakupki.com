<?php

namespace App\Console\Commands;

use App\Jobs\SyncBid;
use App\Model\Bid;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SyncBids extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:bids';

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
        $bids = Bid::where('bids.access_token', '!=', '')
            ->where('participation_url', '')
//            ->join('tenders', 'bids.tender_id', '=', 'tenders.id')
//            ->where('tenders.status', 'active.auction')
            ->get(['bids.id']);
        foreach ($bids as $bidId) {
            $bid = Bid::find($bidId->id);
            $this->info('Dispatch:'.$bid->id);
            $this->info($bid->bidable->tender->status);
            $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
        }
    }
}
