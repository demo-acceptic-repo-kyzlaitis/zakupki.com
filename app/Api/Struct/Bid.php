<?php

namespace App\Api\Struct;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Bid extends Structure
{
    protected $tender;
    protected $bid;
    protected $data = [];

    public function __construct(\App\Model\Bid $bid)
    {
        $this->bid = $bid;
        $tenderId = $bid->bidable->tender->cbd_id;
        $this->uri = '/tenders/'.$tenderId.'/bids';
        if (!empty($bid->cbd_id)) {
            $this->uri .= '/' . $bid->cbd_id . '?acc_token=' . $bid->access_token;
        }
    }


    protected function _setProcuringEntity()
    {
        $organization = $this->bid->organization;
        $data = [
            'contactPoint' => [
                'telephone' => $organization->contact_phone,
                'name' => $organization->contact_name,
                'email' => $organization->contact_email
            ],
            'identifier' => [
                'scheme' => "UA-EDR",
                'id' => $organization->identifier,
                'legalName' => $organization->name
            ],
            'name' => $organization->name,
            'address' => [
                'postalCode' => $organization->postal_code,
                'countryName' => "Україна",
                'streetAddress' => $organization->street_address,
                'region' => $organization->region->region_ua,
                'locality' => $organization->locality,
            ]
        ];

        return $data;

    }

    public function getData()
    {
        $this->data = [
            'tenderers' => [
                $this->_setProcuringEntity()
            ]
        ];
        if (!empty($this->bid->status)) {
            $this->data['status'] = $this->bid->status;
        }
        if ($this->bid->bidable->type == 'tender') {
            $this->data['value'] = [
                'amount' => $this->bid->amount,
                'valueAddedTaxIncluded' => $this->bid->bidable->tax_included,
                'currency' => $this->bid->bidable->currency->currency_code
            ];
        }

        if ($this->bid->bidable->type == 'lot') {
            $this->data['lotValues'][] = [
                'relatedLot' => $this->bid->bidable->cbd_id,
                'value' => [
                    'amount' => $this->bid->amount,
                    'valueAddedTaxIncluded' => $this->bid->bidable->tender->tax_included
                ]
            ];
        }

        if (in_array($this->bid->bidable->tender->type_id, [2, 3, 8, 9, 10, 11, 12])) {
            $this->data['selfEligible'] = $this->bid->self_eligible;
            $this->data['selfQualified'] = $this->bid->self_qualified;
            $this->data['subcontractingDetails'] = $this->bid->subcontracting_details;
//            $this->data['status'] = $this->bid->status;
        }

        foreach ($this->bid->values as $value) {
            $this->data['parameters'][] = [
                'code' => $value->feature->cbd_id,
                'value' => $value->value / 100
            ];
        }

        return $this->data;
    }
}