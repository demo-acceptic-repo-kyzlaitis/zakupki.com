<?php

namespace App\Console\Commands;


use App\Helpers\Mailable;
use App\Model\Agent;
use App\Model\AgentHistory;
use App\Model\Status;
use App\Model\Tender;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Auth;

class TenderAgent extends Command implements SelfHandling
{

    /**
     * params daily
     *        weekly
     * @var string
     */
    protected $signature = 'agent {frequency=daily} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run agents that collect tenders using fillters';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle() {

        $agents = Agent::where('status', 'like', 'active')->where('email_frequency', 'like', (string)$this->argument('frequency'))->with(['codes'])->get();

        foreach ($agents as $agent) {
            $statuses = Status::whereIn('id', $agents[0]->tender_statuses)->get()->pluck('status')->toArray();
            $codes    = $agent->codes->pluck('id');

            $tenders = Tender::whereIn('status', $statuses)->whereHas('items', function ($q) use ($agent) {
                $q->whereIn('region_id', $agent->regions);
            })->whereIn('type_id', $agent->procedure_types)->whereHas('organization', function ($q) use ($agent) {
                $q->whereIn('kind_id', $agent->kinds);
            })->whereBetween('amount', [
                (int)$agent->start_amount,
                (int)$agent->end_amount,
            ])->whereHas('items', function ($q) use ($codes) {
                $q->whereHas('codes', function ($q) use ($codes) {
                    $q->whereIn('codes.id', $codes);
                });
            })->get();


            foreach ($tenders as $tender) {
                AgentHistory::create([
                    'agent_id'  => $agent->id,
                    'tender_id' => $tender->id,
                ]);
            }

            //TODO вынести это куда-то
            $mailable                                     = new Mailable();
            $mailable->message['to']                      = [['email' => $agent->organization->user->email]];
            $mailable->message['merge_vars'][0]['vars'][] = [
                'name'    => 'notificationText',
                'content' => "Ваш пошуковый агент станом на " . Carbon::now()->format('Y-m-d H:i:s') .
                    ' знайшов ' . $tenders->count() .
                    " закупівлі. <br>" . "Щоб переглянути їх перейдіть за <a href=" .
                    route('agent.show', ['id' => $agent->id]) .
                    "> посиланням </a>",
            ];

            $mailable->message['merge_vars'][0]['rcpt']   = $agent->organization->user->email;
            $mailable->message['subject']                 = 'Знайдені закупівлі пошуковим агентом';

            $mandrillMail = new \Weblee\Mandrill\Mail(env('MANDRILL_SECRET'));
            $mandrillMail->messages()->sendTemplate('notify', $mailable->template_content, $mailable->message);

            //gc will collect it later
        }
    }
}