<?php

namespace App\Console\Commands;

use App\Jobs\SyncTender;
use App\Model\Tender;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SyncDate extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:date {date}';

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
        $date = $this->argument('date');
        $tenders = Tender::where('created_at', '>', $date.' 00:00:00')->get();
        foreach ($tenders as $tenderModel) {
            $this->info($tenderModel->cbd_id);
            $this->dispatch((new SyncTender($tenderModel->cbd_id))->onQueue('tenders'));
        }
    }
}
