<?php

namespace App\Console\Commands;

use App\Api\Api;
use Illuminate\Console\Command;


use App\Model\Tender,
    App\Model\Organization,
    App\Model\Currencies,
    App\Model\Bid,
    App\Model\Award,
    App\Model\Contract;

class TendersSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenders:sync {id=0}';

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

    protected function _getOrganization($data)
    {
        $organization = Organization::where('identifier', $data['identifier']['id'])->first();
        if (!$organization) {
            $organizationData = [
                'source' => 1,
                'user_id' => 0,
                'name' => $data['name'],
                'identifier' => $data['identifier']['id'],
                'country_id' => 1,
                'region_id' => 1,
                'postal_code' => isset($data['address']['postalCode']) ? $data['address']['postalCode'] : '',
                'street_address' => isset($data['address']['streetAddress']) ? $data['address']['streetAddress'] : '' ,
                'locality' => isset($data['address']['locality']) ? $data['address']['locality'] : '',
                'contact_name' => $data['contactPoint']['name'],
                'contact_phone' => isset($data['contactPoint']['telephone']) ? $data['contactPoint']['telephone'] : '',
                'contact_email' => isset($data['contactPoint']['email']) ? $data['contactPoint']['email'] : ''
            ];
            $organization = new Organization($organizationData);
            $organization->save();
        }

        return $organization;
    }

    protected function _importAwards($data, $tenderId)
    {
        foreach ($data as $award) {

            $awardModel = Award::where('cbd_id', $award['id'])->first();
            if (!$awardModel) {
                $awardData = [
                    'cbd_id' => $award['id'],
                    'amount' => $award['value']['amount'],
                    'tax_included' => $award['value']['valueAddedTaxIncluded'],
                    'currency_id' => Currencies::where('currency_code', $award['value']['currency'])->first()->id,
                    'created_at' => date('Y-m-d H:i:s', strtotime($award['date'])),
                    'complaint_date_start' => date('Y-m-d H:i:s', strtotime($award['complaintPeriod']['startDate'])),
                ];

                $awardModel = new Award($awardData);
            }

            $awardModel->status = $award['status'];

            $bid = Bid::where('cbd_id', $award['bid_id'])->first();
            $bid->award()->save($awardModel);

            if (!$awardModel->contract && $award['status'] == 'active' && isset($award['contracts'])) {
                foreach ($award['contracts'] as $contract) {
                    $contractModel = Contract::where('cbd_id', $contract['id'])->first();
                    if (!$contractModel) {
                        $contractModel = new Contract([
                            'title' => 'Контракт',
                            'cbd_id' => $contract['id'],
                            'award_id' => $awardModel->id,
                            'tender_id' => $tenderId,
                            'status' => 'pending'
                        ]);
                    }
                    $contractModel->save();
                }
            }
        }
    }

    protected function _importBids($data, $tender)
    {
        foreach ($data as $bid) {
            $tenderer = $bid['tenderers'][0];

            $organization = $this->_getOrganization($tenderer);

            $bidModel = Bid::where('cbd_id', $bid['id'])->first();
            if (!$bidModel) {
                $bidData = [
                    'status' => isset($bid['status']) ? $bid['status'] : '',
                    'participation_url' => isset($bid['participationUrl']) ? $bid['participationUrl'] : '',
                    'cbd_id' => $bid['id'],
                    'amount' => $bid['value']['amount'],
                    'tax_included' => $bid['value']['valueAddedTaxIncluded'],
                    'currency_id' => Currencies::where('currency_code', $bid['value']['currency'])->first()->id
                ];

                $bidModel = new Bid($bidData);
            }

            $organization->bids()->save($bidModel);
            $tender->bids()->save($bidModel);

        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $tenderId = $this->argument('id');
        if ($tenderId == 0) {
            $tenders = Tender::where('cbd_id', '!=', '')->get();
        } else {
            $tenders = Tender::where('id', $tenderId)->get();
        }
        $api = new Api();

        foreach ($tenders as $tender) {
            $response = $api->get($tender->cbd_id, 'tenders');

            $tender->title = $response['data']['title'];
            $tender->tenderID = $response['data']['tenderID'];
            $tender->cbd_id = $response['data']['id'];
            $tender->status = $response['data']['status'];
            $tender->number_of_bids = isset($response['data']['numberOfBids']) ? $response['data']['numberOfBids'] : 0;
            $tender->auction_url = isset($response['data']['auctionUrl']) ? $response['data']['auctionUrl'] : '';
            if (isset($response['data']['auctionPeriod']['startDate'])) $tender->auction_start_date = $response['data']['auctionPeriod']['startDate'];
            if (isset($response['data']['auctionPeriod']['endDate'])) $tender->auction_end_date = $response['data']['auctionPeriod']['endDate'];
            if (isset($response['data']['awardPeriod']['startDate'])) $tender->auction_start_date = $response['data']['awardPeriod']['startDate'];
            if (isset($response['data']['awardPeriod']['endDate'])) $tender->auction_end_date = $response['data']['awardPeriod']['endDate'];
            if (isset($response['data']['bids'])) {
                $this->_importBids($response['data']['bids'], $tender);
            }

            if (isset($response['data']['awards'])) {
                $this->_importAwards($response['data']['awards'], $tender->id);
            }

            $tender->save();
            print "Done\n";
        }
    }
}
