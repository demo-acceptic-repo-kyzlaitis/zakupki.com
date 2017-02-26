<?php

namespace App\Api\Struct;

use App\Model\Award as AwardModel;

class Award extends Structure
{
    protected $tender;
    protected $data = [];

    public function __construct(AwardModel $award)
    {
        $this->award = $award;
        $tender = $award->tender;
        $this->uri = '/tenders/'.$tender->cbd_id.'/awards';
        if (!empty($award->cbd_id)) {
            $this->uri .= '/'.$award->cbd_id;
        }
        $this->uri .= '?acc_token='.$tender->access_token;
    }

    public function getSupplier()
    {
        $organization = $this->award->organization;

        return [
            'contactPoint' => [
                'telephone' => $organization->contact_phone,
                'name' => $organization->contact_name,
                'email' => $organization->contact_email,
            ],
            'identifier' => [
                'scheme' => $organization->identifierScheme,
                'id' => $organization->identifier,
                'legalName' => $organization->name
            ],
            'name' => $organization->name,
            'address' => [
                'postalCode' => $organization->postal_code,
                'countryName' => $organization->country->country_name_ua,
                'streetAddress' => $organization->street_address,
                'region' => $organization->region_name,
                'locality' => $organization->locality,
            ]
        ];
    }


    public function getData()
    {
        $this->data = [
            //'status' => $this->award->status,
            'date' => date('Y-m-d H:i:s', strtotime($this->award->created_at))
        ];

        if ($this->award->tender->procedureType->procurement_method == 'limited') {
            $lots = $this->award->tender->lots;
            $lot = null;
            if (!empty($lots)) {
                $lot = $lots[0];
            }
            $this->data['suppliers'][] = $this->getSupplier();
            $this->data['subcontractingDetails'] =  $this->award->subcontracting_details;
            $this->data['qualified'] =  $this->award->qualified ? true : false;

            if($this->award->tender->procedureType->threshold_type == 'above.limited') {
                if (!is_null($lot)) {
                    $this->data['lotID'] = $lot->cbd_id;
                }
            }

            //$this->data['status'] =  $this->award->status;

            $this->data['value'] = [
                'amount' => $this->award->amount,
                'valueAddedTaxIncluded' => $this->award->tax_included
            ];

        }

        if ($this->award->tender->procedureType->threshold_type == 'above' || $this->award->tender->procedureType->procurement_method == 'selective') {

            $this->data['qualified'] =  $this->award->qualified ? true : false;
            $this->data['eligible'] =  $this->award->eligible ? true : false;

        }

        return $this->data;
    }
}