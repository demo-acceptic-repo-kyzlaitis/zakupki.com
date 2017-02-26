<?php

namespace App\Api\Struct;

use App\Model\Tender as TenderModel;
use Carbon\Carbon;

class Tender extends Structure
{
    protected $tender;
    protected $data = [];

    public function __construct(TenderModel $tender)
    {
        $this->tender = $tender;
        $this->uri = '/tenders';
        if (!empty($tender->cbd_id)) {
            $this->uri .= '/'.$tender->cbd_id.'?acc_token='.$tender->access_token;
        }
    }

    protected function _setData()
    {
        if (!$this->tender->mode) {
            $this->data['mode'] = 'test';
        }

        $this->data['procurementMethod'] = $this->tender->procedureType->procurement_method;
        $this->data['procurementMethodType'] = $this->tender->procedureType->procurement_method_type;

        if (env('APP_ENV') != 'server') {
            $this->data['procurementMethodDetails'] = 'quick, accelerator='.env('API_ACCELERATOR');
            if ($this->data['procurementMethod'] == 'open') {
                $this->data['submissionMethodDetails'] = 'quick(mode:fast-forward), accelerator='.env('API_ACCELERATOR');
            }
        }
        
        if ($this->tender->procedureType->procurement_method == 'open') {
            $this->data['tenderPeriod'] = [
                'startDate' => Carbon::parse($this->tender->tender_start_date)->toIso8601String(),
                'endDate' => Carbon::parse($this->tender->tender_end_date)->toIso8601String(),
            ];
            if ($this->tender->procedureType->procurement_method_type == 'belowThreshold') {
                $this->data['enquiryPeriod'] = [
                    'startDate' => Carbon::parse($this->tender->enquiry_start_date)->toIso8601String(),
                    'endDate' => Carbon::parse($this->tender->enquiry_end_date)->toIso8601String()
                ];
            }
        }
        $this->data['description'] = $this->tender->description;
        $this->data['title'] = $this->tender->title;

        if ($this->data['procurementMethodType'] == 'aboveThresholdEU' || $this->data['procurementMethodType'] == 'competitiveDialogueEU') {
            $this->data['title_en'] = $this->tender->title_en;
        }

        if ($this->data['procurementMethod'] == 'limited' && !empty($this->tender->cause)) {
            $this->data['cause'] = $this->tender->cause;
            $this->data['causeDescription'] = $this->tender->cause_description;
        }
    }

    protected function _setFeatures()
    {
        $features = [];
        $tender = $this->tender;
        if ($tender->features->count() > 0) {
            foreach ($tender->features as $feature) {
                $features[] = $this->_setFeature($feature, 'tenderer');
            }
        }
        foreach ($tender->lots as $lot) {
            if ($lot->cbd_id != '' && $lot->features->count() > 0) {
                foreach ($lot->features as $feature) {
                    $features[] = $this->_setFeature($feature, 'lot');
                }
            }
            foreach ($lot->items as $item) {
                if ($item->lot->cbd_id != '' && $item->features->count() > 0) {
                    foreach ($item->features as $feature) {
                        $features[] = $this->_setFeature($feature, 'item');
                    }
                }
            }
        }

        if (!empty($features)) {
            $this->data['features'] = $features;
        }
    }

    protected function _setFeature($feature, $featureOf)
    {
        $featureData = [
            'code' => $feature->cbd_id,
            'featureOf' => $featureOf,
            'title' => $feature->title,
            'description' => $feature->description
        ];
        if ($featureOf != 'tenderer') {
            $featureData['relatedItem'] = $feature->featureable->cbd_id;
        }
        foreach ($feature->values as $value) {
            $featureData['enum'][] = [
                'value' => $value->value / 100,
                'title' => $value->title
            ];
        }

        return $featureData;
    }



    protected function _setItems()
    {

        $items = $this->tender->allItems;
        foreach ($items as $item) {
            $itemData = [];
            if (!empty($item->cbd_id)) {
                $itemData['id'] = $item->cbd_id;
            }
            if ((($this->tender->procedureType->procurement_method == 'open') ||
                ($this->tender->procedureType->procurement_method_type == 'negotiation.quick' ||$this->tender->procedureType->procurement_method_type == 'negotiation')) &&
                $item->lot->cbd_id != '')  {
                $itemData['relatedLot'] = $item->lot->cbd_id;
            }
            $itemData['unit'] = [
                'code' => $item->unit->code,
                'name' => $item->unit->description,
            ];
            $itemData['description'] = $item->description;

            if ($this->tender->procedureType->procurement_method_type == 'aboveThresholdEU' || $this->tender->procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $this->tender->procedureType->procurement_method_type == 'competitiveDialogueEU') {
                $itemData['description_en'] = $item->description_en;
            }

            $isMainSet = false;
            foreach ($item->codes as $code) {
                if (!$isMainSet && $code->classifier->scheme == 'CPV') {
                    $itemData['classification'] = [
                        'scheme' => $code->classifier->scheme,
                        'description' => $code->description,
                        'id' => $code->code
                    ];
                    $isMainSet = true;
                } else {
                    $itemData['additionalClassifications'][] = [
                        'scheme' => $code->classifier->scheme,
                        'description' => $code->description,
                        'id' => $code->code
                    ];
                }
            }

            if ($item->delivery_date_start)
                $itemData['deliveryDate']['startDate'] = Carbon::parse($item->delivery_date_start)->toIso8601String();
            $itemData['deliveryDate']['endDate'] = Carbon::parse($item->delivery_date_end)->toIso8601String();

            if ($item->same_delivery_address) {
                $itemData['deliveryAddress']['postalCode'] = $item->lot->tender->organization->postal_code;
                $itemData['deliveryAddress']['countryName'] = $item->lot->tender->organization->country->country_name_ua;
                $itemData['deliveryAddress']['region'] =  (isset($item->lot->tender->organization->region)) ? $item->lot->tender->organization->region->region_ua : '';
                $itemData['deliveryAddress']['locality'] =  $item->lot->tender->organization->locality;
                $itemData['deliveryAddress']['streetAddress'] =  $item->lot->tender->organization->street_address;
            } else {
                $itemData['deliveryAddress']['postalCode'] = $item->postal_code;
                $itemData['deliveryAddress']['countryName'] = 'Україна';
                $itemData['deliveryAddress']['region'] =  isset($item->region->region_ua) ? $item->region->region_ua : '';
                $itemData['deliveryAddress']['locality'] =  $item->locality;
                $itemData['deliveryAddress']['streetAddress'] =  $item->delivery_address;

            }

            $itemData['quantity'] = $item->quantity;
            $this->data['items'][] = $itemData;
        }
    }



    protected function _setValue()
    {
        $amount = 0;
        if ($this->tender->procedureType->procurement_method == 'open' ||
            $this->tender->procedureType->procurement_method_type == 'negotiation.quick' ||
            $this->tender->procedureType->procurement_method_type == 'negotiation') {
            foreach ($this->tender->lots as $lot) {
                $amount += $lot->amount;
            }
        } else {
            $amount = $this->tender->amount;
        }
        $this->data['value'] = [
            'currency' => $this->tender->currency->currency_code,
            'amount' => $amount,
            'valueAddedTaxIncluded' => $this->tender->tax_included
        ];
    }

    protected function _setMinStep()
    {
        $this->data['minimalStep'] = [
            'amount' => $this->tender->minimal_step,
            'valueAddedTaxIncluded' => $this->tender->tax_included
        ];
    }

    protected function _setProcuringEntity()
    {
        $organization = $this->tender->organization;
        $this->data['procuringEntity'] = [
            'contactPoint' => [
                'telephone' => $this->tender->contact_phone,
                'name' => $this->tender->contact_name,
                'email' => $this->tender->contact_email,
            ],
            'identifier' => [
                'scheme' => $organization->identifierScheme,
                'id' => $organization->identifier,
                'legalName' => $organization->name
            ],
            'name' => $organization->name,
            'kind' => $organization->kind->kind,
            'address' => [
                'postalCode' => $organization->postal_code,
                'countryName' => $organization->country->country_name_ua,
                'streetAddress' => $organization->street_address,
                'region' => (isset($organization->region)) ? $organization->region->region_ua : '',
                'locality' => $organization->locality,
            ]
        ];
        /*if ($this->tender->procedureType->procurementMethodType == 'aboveThreshold'
                || $this->tender->procedureType->procurementMethodType == 'aboveThresholdEU') {
            $this->data['procuringEntity']['identifier']['legalName'] = $organization->legal_name;
        }*/
        if ($this->tender->type_id == 2 || $this->tender->type_id == 3 || $this->tender->type_id == 10) {
            $this->data['procuringEntity']['identifier']['legalName'] = $organization->legal_name;
        }
        if ($this->tender->type_id == 3 || $this->tender->type_id == 10) {
            $this->data['procuringEntity']['identifier']['legalName_en'] = $organization->legal_name_en;
            $this->data['procuringEntity']['contactPoint']['name_en'] = $this->tender->contact_name_en;
            $this->data['procuringEntity']['name_en'] = $organization->legal_name_en;
        }

//        if (!empty($organization->contact_url)) {
//            $this->data['procuringEntity']['contactPoint']['url'] = $organization->contact_url;
//        }
    }

    public function getData()
    {
        if ($this->tender->procedureType->procurement_method == 'selective') {
            if (env('APP_ENV') != 'server') {
                $this->data['procurementMethodDetails'] = 'quick, accelerator='.env('API_ACCELERATOR');
                $this->data['submissionMethodDetails'] = 'quick(mode:fast-forward), accelerator='.env('API_ACCELERATOR');
            }
            $this->data['tenderPeriod']['endDate'] = Carbon::parse($this->tender->tender_end_date)->toIso8601String();

            $items = $this->tender->allItems;
            foreach ($items as $item) {
                $itemData = ['id' => $item->cbd_id];
                $itemData['deliveryDate']['startDate'] = Carbon::parse($item->delivery_date_start)->toIso8601String();
                $itemData['deliveryDate']['endDate'] = Carbon::parse($item->delivery_date_end)->toIso8601String();

                $this->data['items'][] = $itemData;
            }
        } else {
            $this->_setData();
            $this->_setFeatures();
            $this->_setItems();
            if ($this->tender->procedureType->procurement_method == 'open') {
                $this->_setMinStep();
            }

            $this->_setValue();
            $this->_setProcuringEntity();

            if ($this->tender->procedureType->procurement_method == 'open' ||
                $this->tender->procedureType->procurement_method_type == 'negotiation.quick' ||
                $this->tender->procedureType->procurement_method_type == 'negotiation') {
                foreach ($this->tender->lots as $lot) {
                    if ($lot->cbd_id != '') {
                        $lotS = new Lot($lot); //Lot -> Structure
                        $this->data['lots'][] = $lotS->getData();
                    }
                }
            }
        }

        return $this->data;
    }
}