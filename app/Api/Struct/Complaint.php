<?php

namespace App\Api\Struct;

use App\Model\Award;
use App\Model\Bid;
use App\Model\Complaint as ComplaintModel;
use App\Model\Organization;

class Complaint extends Structure
{
    public $tender;
    public $organization;
    protected $data = [];

    public function __construct(ComplaintModel $complaint, Organization $organization)
    {
        $this->complaint = $complaint;
        if ($complaint->complaintable->type == 'award'){
            $tender = $complaint->complaintable->tender;
            $this->uri = '/tenders/'.$tender->cbd_id.'/awards/'.$complaint->complaintable->cbd_id.'/complaints';
        } elseif ($complaint->complaintable->type == 'qualification') {
            $tender = $complaint->complaintable->bid->tender;
            $this->uri = '/tenders/' . $tender->cbd_id . '/qualifications/' . $complaint->complaintable->cbd_id . '/complaints';
        } else {
            $tender = $complaint->complaintable->tender;
            $this->uri = '/tenders/'.$tender->cbd_id.'/complaints';
        }

        if (!empty($complaint->cbd_id)) {
            $this->uri .= '/'.$complaint->cbd_id;
            if ($organization->type == 'customer') {
                $this->uri .= '?acc_token=' . $tender->access_token;
            } else {
                $this->uri .= '?acc_token='.$complaint->access_token;
            }
        } elseif ($complaint->complaintable->type == 'award' && $tender->procedureType->threshold_type != 'above.limited') {
            if($complaint->complaintable->bid->access_token == "") {
                $this->uri .= '?acc_token='. $complaint->complaintable->bid->bidable->bids()->where('organization_id', $organization->id)->first()->access_token;
            } else {
                $this->uri .= '?acc_token='.$complaint->complaintable->bid->access_token;
            }
        } elseif ($complaint->complaintable->type == 'qualification') {
            $this->uri .= '?acc_token=' . $complaint->complaintable->bid->access_token;
        }
    }

    protected function _setProcuringEntity()
    {
        $organization = $this->complaint->organization;
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
        $complaint = $this->complaint;
        if ($complaint->status == 'pending' || $complaint->status == 'resolved' ) {
            $this->data = [
                'status' => $complaint->status,
                'satisfied' => $complaint->status === 'resolved' ? true : false,
                'tendererAction' => $complaint->tenderer_action
            ];
        }
        if ($complaint->status == 'draft') {
            $this->data = [
                'title' => $this->complaint->title,
                'description' => $this->complaint->description,
                'author' => $this->_setProcuringEntity(),
                'status' => 'draft'
            ];
        }
        if ($complaint->status === 'claim') {
            $this->data = [
                'status' => 'claim'
            ];
        }
        if ($complaint->status === 'cancelled') {
            $this->data = [
                'status' => 'cancelled'
            ];
            if (!empty($complaint->cancellation_reason)) {
                $this->data['cancellationReason'] = $complaint->cancellation_reason;
            }
        }
        if ($complaint->status === 'answered') {
            $this->data = [
                'status' => 'answered',
                'resolution' => $this->complaint->resolution,
                'resolutionType' => $this->complaint->resolution_type,
            ];
        }
        if ($complaint->status == 'stopping') {
            $this->data = [
                'status' => 'stopping',
                'cancellationReason' => $this->complaint->cancellation_reason,
            ];
        }
        if ($complaint->status == 'resolved') {
            return ['status' => 'resolved', 'satisfied' => 'true', 'tendererAction' => $complaint->tenderer_action];
        }
//            if ($complaint->complaintable->type == 'qualification') {
//                $this->data = [
//                    'title' => $this->complaint->title,
//                    'description' => $this->complaint->description,
//                    'author' => $this->_setProcuringEntity(),
//                    'satisfied' => $complaint->status == 'resolved' ? true : false,
//                    'status' => 'pending'
//                ];
//            } else {
//                $this->data = [
//                    'status' => $complaint->status,
//                    'satisfied' => $complaint->status == 'resolved' ? true : false,
//                    'tendererAction' => $complaint->tenderer_action
//                ];
//            }

//        }

        return $this->data;



    }
}