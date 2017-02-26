<?php

namespace App\Console\Commands;

use App\Api\Api;
use Illuminate\Console\Command,
    App\Model\Tender,
    \App\Model\Question;

class TendersGetQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenders:getquestions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
        $tenders = Tender::where('status', 'active.enquiries')->where('enquiry_end_date', '>', date('Y-m-d H:i:s'))->get();
        $api = new Api();
        foreach ($tenders as $tender) {
            $response = $api->get($tender->cbd_id, 'questions');
            var_dump($response);
            foreach ($response['data'] as $item) {
                $question = Question::where('cbd_id', $item['id'])->first();
                if (!$question) {
                    $question = new Question();
                    $question->tender_id = $tender->id;
                    $question->cbd_id = $item['id'];
                    $question->title = $item['title'];
                    $question->created_at = \Carbon\Carbon::parse($item['date'])->format('Y-m-d H:i:s');
                    $question->description = $item['description'];
                }
                $question->answer = isset($item['answer']) ? $item['answer'] : '';
                $question->save();
            }
        }
    }
}
