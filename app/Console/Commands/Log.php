<?php

namespace App\Console\Commands;

use App\Api\Struct\Bid;
use App\Model\Organization;
use App\Model\Question;
use App\Model\Tender;
use App\Model\User;
use Illuminate\Console\Command;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Log extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log';
    public $f;

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
//        parent::__construct();
//        $this->f =  fopen('storage/logs/actions.log', 'a');

    }

    public function log($date, $data)
    {
//        foreach ($data as $key => $value) {
//            if (is_array($value)) {
//                $data[$key] = json_encode($value);
//            }
//        }
//        fputs($this->f, '['.date('Y-m-d H:i:s', strtotime($date)).'] '.implode("\t", $data)."\r\n");
    }

    public function add($date, $seconds)
    {
        return date('Y-m-d H:i:s', strtotime($date) + $seconds);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$this->_logger = new Logger('View Logs');
        $this->_logger->pushHandler(new StreamHandler('storage/logs/actions.log', Logger::INFO));

        $org = Organization::find(4);
        $user = $org->user;

        $tender = Tender::with('lots')->find('5833');

        $session = md5('5833');

        $tenderData2 = $tenderData = $tender->toArray();
        unset($tenderData['id']);
        unset($tenderData['tenderID']);
        unset($tenderData['number_of_bids']);
        unset($tenderData['auction_url']);
        unset($tenderData['auction_start_date']);
        unset($tenderData['auction_end_date']);
        unset($tenderData['award_start_date']);
        unset($tenderData['award_end_date']);
        unset($tenderData['cbd_id']);
        unset($tenderData['date_modified']);
        unset($tenderData['published_at']);
        unset($tenderData['created_at']);
        unset($tenderData['access_token']);
        unset($tenderData['status']);

        foreach ($tender->lots as $index => $lot) {
            $tenderData['lots'][$index] = $lot;
            foreach ($lot->items as $item) {
                $tenderData['lots'][$index]['items'][] = $item;
            }
        }







        $this->log($this->add($tender->created_at, -30), ['REQUEST:GET', 'USER:'.$user->id, 'tender.create', '$session:'.$session, []]);
        $this->log($this->add($tender->created_at, 0), ['REQUEST:POST', 'USER:'.$user->id, 'tender.store', '$session:'.$session, $tenderData]);

        $this->log($this->add($tender->created_at, 20), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 25), ['REQUEST:GET', 'USER:'.$user->id, 'tender.published', '$session:'.$session, ['id' => $tender->id]]);

        $this->log($this->add($tender->created_at, 56), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 60), ['REQUEST:GET', 'USER:'.$user->id, 'tender.edit', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 90), ['REQUEST:POST', 'USER:'.$user->id, 'tender.update', '$session:'.$session, $tenderData]);

        $this->log($this->add($tender->created_at, 100), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);

        $this->log($this->add($tender->created_at, 110), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, []]);
        $this->log($this->add($tender->created_at, 115), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000046']]);
        $this->log($this->add($tender->created_at, 120), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 123), ['REQUEST:POST', 'USER:'.$user->id, 'tender.update', '$session:'.$session, $tenderData]);

        $this->log($this->add($tender->created_at, 178), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 180), ['REQUEST:GET', 'USER:'.$user->id, 'tender.edit', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 185), ['REQUEST:POST', 'USER:'.$user->id, 'tender.update', '$session:'.$session, $tenderData]);
        $this->log($this->add($tender->created_at, 187), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);

        $this->log($this->add($tender->created_at, 190), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, []]);
        $this->log($this->add($tender->created_at, 193), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000046']]);
        $this->log($this->add($tender->created_at, 195), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);
        $this->log($this->add($tender->created_at, 197), ['REQUEST:GET', 'USER:'.$user->id, 'tender.question.list', '$session:'.$session, ['id' => $tender->id]]);

        $question = Question::find(681);
        $questionData = $question->toArray();
        $this->log($this->add($tender->created_at, 200), ['REQUEST:POST', 'USER:'.$user->id, 'question.store', '$session:'.$session, ['answer' => $questionData['answer']]]);
        $this->log($this->add($tender->created_at, 204), ['REQUEST:GET', 'USER:'.$user->id, 'tender.question.list', '$session:'.$session, $tenderData]);
        $this->log($this->add($tender->created_at, 207), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);



        $org = Organization::find(95714);
        $user = User::find(20);

        $tender = Tender::with('lots')->find('5835');
        unset($tenderData['id']);
        unset($tenderData['tenderID']);
        unset($tenderData['number_of_bids']);
        unset($tenderData['auction_url']);
        unset($tenderData['auction_start_date']);
        unset($tenderData['auction_end_date']);
        unset($tenderData['award_start_date']);
        unset($tenderData['award_end_date']);
        unset($tenderData['cbd_id']);
        unset($tenderData['date_modified']);
        unset($tenderData['published_at']);
        unset($tenderData['created_at']);
        unset($tenderData['access_token']);
        unset($tenderData['status']);

        foreach ($tender->lots as $index => $lot) {
            $tenderData['lots'][$index] = $lot;
            foreach ($lot->items as $item) {
                $tenderData['lots'][$index]['items'][] = $item;
            }
        }

        $session = md5('5835');

        $bid = \App\Model\Bid::find('5871');

        $bidData = $bid->toArray();
        unset($bidData['id']);
        unset($bidData['cbd_id']);
        unset($bidData['organization_id']);
        unset($bidData['tender_id']);
        unset($bidData['bidable_id']);
        unset($bidData['bidable_type']);
        unset($bidData['currency_id']);
        unset($bidData['status']);
        unset($bidData['access_token']);
        unset($bidData['participation_url']);
        unset($bidData['created_at']);
        unset($bidData['updated_at']);

        $question = Question::find(682);
        $questionData = $question->toArray();
        unset($questionData['id']);
        unset($questionData['cbd_id']);
        unset($questionData['organization_id']);
        unset($questionData['organization_to_id']);
        unset($questionData['questionable_id']);
        unset($questionData['tender_id']);
        unset($questionData['questionable_type']);
        unset($questionData['answer']);
        unset($questionData['created_at']);
        unset($questionData['updated_at']);


        $this->log($this->add($question->created_at, -10), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($question->created_at, -8), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($question->created_at, -5), ['REQUEST:GET', 'USER:'.$user->id, 'question.create', '$session:'.$session, []]);
        $this->log($this->add($question->created_at, 0), ['REQUEST:POST', 'USER:'.$user->id, 'question.store', '$session:'.$session, $questionData]);
        $this->log($this->add($question->created_at, 5), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);

        $_bidData = $bidData;
        $_bidData['amount'] = rand(0,5000);
        $_bidData['minimal_step'] = rand(0,100);

        $this->log($this->add($question->created_at, 30), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($question->created_at, 32), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($question->created_at, 35), ['REQUEST:GET', 'USER:'.$user->id, 'bid.create', '$session:'.$session, []]);
        $this->log($this->add($question->created_at, 37), ['REQUEST:POST', 'USER:'.$user->id, 'bid.store', '$session:'.$session, $_bidData]);

        $_bidData = $bidData;
        $_bidData['amount'] = rand(0,5000);
        $_bidData['minimal_step'] = rand(0,100);

        $this->log($this->add($question->created_at, 240), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($question->created_at, 242), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($question->created_at, 243), ['REQUEST:GET', 'USER:'.$user->id, 'bid.create', '$session:'.$session, []]);
        $this->log($this->add($question->created_at, 245), ['REQUEST:POST', 'USER:'.$user->id, 'bid.store', '$session:'.$session, $_bidData]);
        $this->log($this->add($question->created_at, 248), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $this->log($this->add($question->created_at, 900), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($question->created_at, 901), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($question->created_at, 903), ['REQUEST:DELETE', 'USER:'.$user->id, 'bid.delete', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($question->created_at, 904), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($question->created_at, 905), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);


        $_bidData = $bidData;
        $_bidData['amount'] = rand(0,5000);
        $_bidData['minimal_step'] = rand(0,100);

        $this->log($this->add($bid->created_at, -10), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($bid->created_at, -8), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($bid->created_at, -5), ['REQUEST:GET', 'USER:'.$user->id, 'bid.create', '$session:'.$session, []]);
        $this->log($this->add($bid->created_at, 0), ['REQUEST:POST', 'USER:'.$user->id, 'bid.store', '$session:'.$session, $_bidData]);
        $this->log($this->add($bid->created_at, 3), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);


        $_bidData = $bidData;
        $_bidData['amount'] = '5000.99';

        $this->log($this->add($bid->created_at, 10), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($bid->created_at, 12), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($bid->created_at, 13), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($bid->created_at, 14), ['REQUEST:POST', 'USER:'.$user->id, 'bid.update', '$session:'.$session, $_bidData]);
        $this->log($this->add($bid->created_at, 16), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $_bidData = $bidData;
        $_bidData['amount'] = '10';

        $this->log($this->add($bid->created_at, 20), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($bid->created_at, 22), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($bid->created_at, 23), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($bid->created_at, 24), ['REQUEST:POST', 'USER:'.$user->id, 'bid.update', '$session:'.$session, $_bidData]);
        $this->log($this->add($bid->created_at, 26), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);


        $this->log($this->add($bid->created_at, 20), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($bid->created_at, 22), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($bid->created_at, 23), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($bid->created_at, 24), ['REQUEST:POST', 'USER:'.$user->id, 'bid.update', '$session:'.$session, $_bidData]);
        $this->log($this->add($bid->created_at, 26), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $this->log($this->add($bid->created_at, 30), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($bid->created_at, 32), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($bid->created_at, 33), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($bid->created_at, 34), ['REQUEST:PUT', 'USER:'.$user->id, 'document.upload', '$session:'.$session,  ['id' => $bid->id]]);
        $this->log($this->add($bid->created_at, 36), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $this->log($this->add($bid->created_at, 40), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($bid->created_at, 41), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($bid->created_at, 42), ['REQUEST:GET', 'USER:'.$user->id, 'question.create', '$session:'.$session, []]);
        $this->log($this->add($bid->created_at, 43), ['REQUEST:POST', 'USER:'.$user->id, 'question.store', '$session:'.$session, $questionData]);
        $this->log($this->add($bid->created_at, 44), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);

        $this->log($this->add($tender->tender_end_date, 20), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($tender->tender_end_date, 22), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($tender->tender_end_date, 23), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($tender->tender_end_date, 24), ['REQUEST:POST', 'USER:'.$user->id, 'bid.update', '$session:'.$session, $_bidData]);
        $this->log($this->add($tender->tender_end_date, 26), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $_bidData = $bidData;
        $_bidData['amount'] = '1';
        $this->log($this->add($tender->tender_end_date, 30), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($tender->tender_end_date, 32), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($tender->tender_end_date, 33), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($tender->tender_end_date, 34), ['REQUEST:POST', 'USER:'.$user->id, 'bid.update', '$session:'.$session, $_bidData]);
        $this->log($this->add($tender->tender_end_date, 36), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $this->log($this->add($tender->tender_end_date, 50), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($tender->tender_end_date, 51), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($tender->tender_end_date, 53), ['REQUEST:DELETE', 'USER:'.$user->id, 'bid.delete', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($tender->tender_end_date, 54), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($tender->tender_end_date, 55), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, ['id' => $tender->id]]);

        $this->log($this->add($tender->tender_end_date, 61), ['REQUEST:GET', 'USER:'.$user->id, 'tender.list', '$session:'.$session, ['s' => 'UA-2016-04-21-000179-1']]);
        $this->log($this->add($tender->tender_end_date, 62), ['REQUEST:GET', 'USER:'.$user->id, 'tender.show', '$session:'.$session, $tenderData]);
        $this->log($this->add($tender->tender_end_date, 63), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);
        $this->log($this->add($tender->tender_end_date, 64), ['REQUEST:PUT', 'USER:'.$user->id, 'document.upload', '$session:'.$session,  ['id' => $bid->id]]);
        $this->log($this->add($tender->tender_end_date, 66), ['REQUEST:GET', 'USER:'.$user->id, 'bid.edit', '$session:'.$session, ['id' => $bid->id]]);

        $this->log($this->add($tender->tender_end_date, 100), ['REQUEST:GET', 'USER:'.$user->id, 'bids.list', '$session:'.$session, []]);*/











    }
}
