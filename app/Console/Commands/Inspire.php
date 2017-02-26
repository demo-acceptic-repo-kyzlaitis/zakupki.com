<?php

namespace App\Console\Commands;

use App\Api\Api;
use App\Api\Struct\ContractDocument;
use App\Jobs\GetContractCredentials;
use App\Jobs\SyncContract;
use App\Jobs\SyncTender;
use App\Model\Contract;
use App\Model\ContractDocuments;
use App\Model\Notification;
use App\Model\Organization;
use App\Model\Tender;
use App\Model\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class Inspire extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $contracts = Contract::where('amount_paid', 2147483647)->get();
//        foreach ($contracts as $contract) {
//            $job = (new SyncContract($contract->cbd_id))->onQueue('tenders');
//            $this->dispatch($job);
//        }
//        die;
//        $this->dispatch((new SyncTender(0))->onQueue('tenders'));
//        die;

        $organizations = Organization::where('user_id', '>', 0)->where('source', '!=', 2)->where('type', 'supplier')->get();
        foreach ($organizations as $organization) {
            if ($organization->user && $organization->user->id) {
                Notification::create([
                    'user_id' => $organization->user->id,
                    'title' => 'Вебінар',
                    'text' => 'Вебінар:<br><br>
УЧАСНИКАМ: Початок роботи в системі PROZORRO. Подача пропозиції на Zakupki.UA<br><br>
<a href="https://room.etutorium.com/registert/6/1877382d5a7f70681f270a4b5a7f70681f273a94">https://room.etutorium.com/registert/6/1877382d5a7f70681f270a4b5a7f70681f273a94</a>',
                    'alias' => 'ad',
                    'sended_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        die;

        $complaint = Complaint::where('id', 115)->firstOrFail();

        $contract = Contract::find(107596);
        $this->dispatch(new GetContractCredentials($contract->cbd_id));
        die;

        $api = new Api(false);
        $documentStructure = new ContractDocument(ContractDocuments::find(125632));
        $api->patch($documentStructure);



        $user = User::find(859);
        $user->password = bcrypt('8pP2iS3fD');

        $user->save();
        die;

        $tenders = Tender::where('source', 1)->get();
        foreach ($tenders as $tender) {
            $this->info($tender->id);
            if ($tender->contract && $tender->contract->access_token == '') {
                $this->dispatch(new GetContractCredentials($tender->contract->cbd_id));
            }
        }

        exit();

//        $tenders = Tender::where('source', 1)->get();
//        foreach ($tenders as $tender) {
//            $this->info($tender->cbd_id);
//            Artisan::call('sync', [
//                'id' => $tender->cbd_id
//            ]);
//        }
//        die;

//        $awards = Award::get();
//        foreach ($awards as $award) {
//            $award->tender_id = $award->bid->tender->id;
//            $award->save();
//        }
//        die;
//        $tenders = Tender::where('access_token', '!=' , '')->get();
//        foreach ($tenders as $tender) {
//            $lotModel = $tender->lots()->first();
//            $lotModel->title = $tender->title;
//            $lotModel->description = $tender->description;
//            $lotModel->amount = $tender->amount;
//            $lotModel->minimal_step = $tender->minimal_step;
//            $lotModel->save();
//        }
//        die;

        $tenders = Tender::where('access_token', '')->get();
        foreach ($tenders as $tender) {
            $this->info($tender->id);
            $tender->questions()->delete();
            $tender->documents()->delete();
            foreach ($tender->complaints as $complaint) {
                $complaint->documents()->delete();
            }
            $tender->complaints()->delete();
            $tender->cancel->documents()->delete();
            $tender->cancel()->delete();


//            foreach ($tender->allBids as $bid) {
//                $bid->award->documents()->delete();
//                $bid->award()->delete();
//                $bid->documents()->delete();
//                Award::where('bid_id', $bid->id)->delete();
//            }

//            $tender->bids()->delete();

            foreach ($tender->items as $item) {
                $item->codes()->sync([]);
                $item->delete();
            }

            $tender->delete();

        }




    }
}
