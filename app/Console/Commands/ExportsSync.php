<?php

namespace App\Console\Commands;

use App\Model\Export as ExportTable;
use App\Model\Tender;
use Illuminate\Console\Command;

class ExportsSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'синхронизирует таблицу exports с таблицей tenders, для определения по какому тендеру необходимо сделать экспорт протоколов, результатов и других документов,
                        тендеры выбираются по полю send_to_import со значением null, определение что нужно экспортировать зависит от статуса тендера';

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
        /*
         * Статусы результатов  complete
         * */
        $this->info("START");

        /*
         * Получить однолотовые тендеры
         * */
        $noLotTenders = $this->getNoLotTenders();
        $this->info("FETCHED TENDER WITH NO LOTS " . $noLotTenders->count());
        $this->exportAs('result', $noLotTenders);


        $multiLotTenders = $this->getMultiLotTenders();
        $this->info("FETCHED TENDER WITH LOTS " . $multiLotTenders->count());
        $this->exportAs('result_by_lot', $multiLotTenders);


        $this->info("DONE");

    }

    protected function getNoLotTenders()
    {
        $tenders = Tender::open()
            ->whereNull('send_to_import')
            ->where('mode', 1)
            ->where('multilot', 0)
            ->where('tenderID', '!=', '')
            ->whereIn('status', ['complete', 'unsuccessful', 'cancelled'])
            ->get();
        return $tenders;
    }

    protected function getMultiLotTenders()
    {
        $tenders = Tender::with(['lots' => function ($q) {
            $q->whereIn('status', ['complete', 'unsuccessful', 'cancelled']);
        }])
            ->open()
            ->whereNull('send_to_import')
            ->where('mode', 1)
            ->where('multilot', 1)
            ->where('tenderID', '!=', '')
            ->get();
        return $tenders;
    }

    protected function exportAs($feed_type, $tenders)
    {
        foreach ($tenders as $tender) {

            switch ($feed_type) {
                case 'result':
                    ExportTable::create([
                        'item_id'   => $tender->id,
                        'feed_type' => $feed_type,
                    ]);
                    $this->info("CREATED RECORD FOR FEED {$feed_type} BY TENDER ID {$tender->id}");
                    break;
                case 'result_by_lot':
                    foreach ($tender->lots as $lot) {
                        ExportTable::create([
                            'item_id'   => $lot->id,
                            'feed_type' => $feed_type,
                        ]);
                        $this->info("CREATED RECORD FOR FEED {$feed_type} BY TENDER ID {$lot->id}");
                    }
                    break;
            }
        }
    }
}
