<?php

namespace App\Api\Struct;

use App\Model\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Question extends Structure
{
    protected $data = [];
    public $question;

    public function __construct($question)
    {
        $this->question = $question;
        if ($question->questionable->type == 'item') {
            $cbdId = $question->questionable->lot->tender->cbd_id;
            $accessToken = $question->questionable->lot->tender->access_token;
        } elseif ($question->questionable->type == 'lot') {
            $cbdId = $question->questionable->tender->cbd_id;
            $accessToken = $question->questionable->tender->access_token;
        } else {
            $cbdId = $question->questionable->cbd_id;
            $accessToken = $question->questionable->access_token;
        }

        $this->access_token = $accessToken;

        $this->uri = '/tenders/'.$cbdId.'/questions';

        if (!empty($question->answer)) {
            $this->uri .= '/'.$question->cbd_id;
        }

        $this->uri .= '?acc_token='.$accessToken;
    }

    public function getData()
    {
        if (!empty($this->question->answer)) {
            $data = [
                'answer' => $this->question->answer
            ];
        } else {
            $data = [
                'title' => $this->question->title,
                'description' => $this->question->description,
                'questionOf' => $this->question->questionable->type
            ];
            if ($this->question->questionable->type != 'tender') {
                $data['relatedItem'] = $this->question->questionable->cbd_id;
            }
            if (!empty($this->question->organization_id)) {
                $organization = Organization::find($this->question->organization_id);
                $data['author'] = [
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
            }

        }

        return $data;
    }

}