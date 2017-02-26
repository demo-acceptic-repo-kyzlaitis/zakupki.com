<?php

namespace App\Console\Commands;

use App\Model\Bid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class BidsLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bids:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search bids without links';

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
        if (env('APP_ENV' == 'server')) {
            $bids = Bid::where('participation_url', '')->whereHas('tender', function ($q) {
                $q->where('status', 'active.auction');
            })->get();
            if ($bids->count() > 0) {
                foreach ($bids as $bid) {
                    Mail::queue('emails.bid-links', ['bid' => $bid], function ($message) {
                        $message->to('spasibova@zakupki.com.ua', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
                        $message->to('manager@zakupki.com.ua', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
                        //$message->to('dex.maks@mail.ru', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
                    });
                }
            }
        }
    }
}
