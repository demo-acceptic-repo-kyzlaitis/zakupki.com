<?php

namespace App\Console\Commands;

use App\Model\Export as ExportTable;
use Illuminate\Console\Command;

class ExportsSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'отправляет на экспорт все что  в таблице exports имеет sent_to_export равное null';

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
        $this->info("START");
        /* выбираем все записи в  таблице exports где sent_to_export равное null */
        $itemsForExport = ExportTable::whereNull('sent_to_export')->get();

        $this->info("FETCHED ITEMS " . $itemsForExport->count());

        foreach ($itemsForExport as $item) {
            $client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);
            $response = $client->get("http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type={$item->feed_type}&id={$item->item_id}");
            $body = (string)$response->getBody();
            if ($body == '1') {
                $item->sent_to_export = date('Y-m-d H:i:s');
                $item->save();
                $this->info("http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type={$item->feed_type}&id={$item->item_id}");
            } else {
                $this->error("http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type={$item->feed_type}&id={$item->item_id}");
            }
        }
    }
}
