<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Api\Api;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Model\Tender,
    App\Model\Organization,
    App\Model\Currencies,
    App\Model\Bid,
    App\Model\Award;

class UpdateTender extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $cbdId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($cbdId)
    {
        $this->cbdId = $cbdId;
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
                'street_address' => $data['address']['streetAddress'],
                'locality' => isset($data['address']['locality']) ? $data['address']['locality'] : '',
                'contact_name' => $data['contactPoint']['name'],
                'contact_phone' => $data['contactPoint']['telephone'],
                'contact_email' => $data['contactPoint']['email']
            ];
            $organization = new Organization($organizationData);
            $organization->save();
        }

        return $organization;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = new Api();

        $data = $api->get($this->cbdId, 'tenders')['data'];

        $organization = Organization::where('identifier', $data['procuringEntity']['identifier']['id'])->where('source', 1)->first();
        if (!$organization) {
            $organizationData = [
                'source' => 1,
                'user_id' => 0,
                'name' => $data['procuringEntity']['name'],
                'identifier' => $data['procuringEntity']['identifier']['id'],
                'country_id' => 1,
                'region_id' => \App\Model\TendersRegions::where('region_ua', $data['procuringEntity']['address']['region'])->orWhere('region_name', $data['procuringEntity']['address']['region'])->first()->id,
                'postal_code' => isset($data['procuringEntity']['address']['postalCode']) ? $data['procuringEntity']['address']['postalCode'] : '',
                'street_address' => isset($data['procuringEntity']['address']['streetAddress']) ? $data['procuringEntity']['address']['streetAddress'] : '',
                'locality' => $data['procuringEntity']['address']['locality'],
                'contact_name' => $data['procuringEntity']['contactPoint']['name'],
                'contact_phone' => $data['procuringEntity']['contactPoint']['telephone'],
                'contact_email' => $data['procuringEntity']['contactPoint']['email']
            ];
            $organization = new Organization($organizationData);
            $organization->save();
        }

        $tenderData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'amount' => $data['value']['amount'],
            'currency_id' => \App\Model\Currencies::where('currency_code', $data['value']['currency'])->first()->id,
            'tax_included' => $data['value']['valueAddedTaxIncluded'],
            'minimal_step' => $data['minimalStep']['amount'],
            'enquiry_start_date' => $data['enquiryPeriod']['startDate'],
            'enquiry_end_date' => $data['enquiryPeriod']['endDate'],
            'tender_start_date' => $data['tenderPeriod']['startDate'],
            'tender_end_date' => $data['tenderPeriod']['endDate'],
            'status' => $data['status'],
            'cbd_id' => $this->cbdId
        ];
        $tender = Tender::where('cbd_id', $this->cbdId)->first();

        if (is_null($tender)) {
            $tender = new Tender($tenderData);
            $organization->tenders()->save($tender);
        } else {
            $tender->update($tenderData);
        }

        $items = $tender->items;
        var_dump($tender->id);

        foreach ($data['items'] as $index => $item) {
            $itemData = [
                "description" => $item['description'],
                "quantity" => $item['quantity'],
                "unit_id" => \App\Model\Units::where('code', $item['unit']['code'])->first()->id,
                "delivery_date_start" =>  $item['deliveryDate']['startDate'],
                "delivery_date_end" => $item['deliveryDate']['endDate'],
                'country_id' => 1,
                "region_id" => \App\Model\TendersRegions::where('region_ua', $item['deliveryAddress']['region'])->orWhere('region_name', $data['procuringEntity']['address']['region'])->first()->id,
                "postal_code" => isset($item['deliveryAddress']['postalCode']) ? $item['deliveryAddress']['postalCode'] : '',
                "locality" => $item['deliveryAddress']['locality'],
                "delivery_address" => $item['deliveryAddress']['streetAddress']
            ];

            if (isset($items[$index])) {
                $itemModel = $items[$index];
                $itemModel->update($itemData);
            } else {
                $itemModel = new \App\Model\Item($itemData);
                $tender->items()->save($itemModel);
            }

            $codes[] = \App\Model\Codes::where('code', $item['classification']['id'])->first()->id;
            foreach ($item['additionalClassifications'] as $additionalClassification) {
                $codes[] = \App\Model\Codes::where('code', $additionalClassification['id'])->first()->id;
            }
            var_dump($itemModel->id);
            var_dump($codes);

            $itemModel->codes()->sync($codes);
        }


        $tender->save();
        print "Done\n";
    }
}
