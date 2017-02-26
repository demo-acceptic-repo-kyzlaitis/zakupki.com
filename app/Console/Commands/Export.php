<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Tender;

class Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export';

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
        $tenders = Tender::open()->whereNull('send_to_import')->where('mode', 1)->where('tenderID', '!=', '')->get();
        foreach ($tenders as $tender) {
            $this->info($tender->id);
            if (strtotime($tender->enquiry_start_date) > strtotime('2016-06-13')) {
//                $this->info($tender->created_at);
//                $this->info($tender->enquiry_start_date);
//                continue;
                $client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);
                $response = $client->get('http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type=tender&id=' . $tender->id);
                $body = (string)$response->getBody();
                if ($body == 1) {
                    $tender->send_to_import = date('Y-m-d H:i:s');
                    $tender->save();
                    $this->info('http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type=tender&id=' . $tender->id);
                } else {
                    $this->error('http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type=tender&id=' . $tender->id);
                }
            } else {
                $tender->send_to_import = date('Y-m-d H:i:s');
                $tender->save();
            }
        }
    }
}
