<?php

namespace App\Api\Struct;

use Carbon\Carbon;

class Plan extends Structure
{
    protected $plan;
    protected $data = [];

    public function __construct(\App\Model\Plan $plan)
    {
        $this->plan = $plan;
        $this->uri = '/plans';
        if (!empty($plan->cbd_id)) {
            $this->uri .= '/'.$plan->cbd_id.'?acc_token='.$plan->access_token;
        }
    }

    protected function _setData()
    {
        if (!$this->plan->organization->mode) {
            $this->data['mode'] = 'test';
        }
        $this->data['tender']['procurementMethod'] = $this->plan->procedure->procurement_method;
        $this->data['tender']['procurementMethodType'] = $this->plan->procedure->procurement_method_type;
        $this->data['tender']['tenderPeriod']['startDate'] = $this->plan->start_year.'-'.$this->plan->start_month.'-01'.'T00:00:00+0200';

        $this->data['classification'] = [
            "scheme" => "CPV",
            'description' => $this->plan->code->description,
            'id' => $this->plan->code->code
        ];

        if ($this->plan->codeAdditional) {
            $this->data['additionalClassifications'][] = [
                "scheme" => $this->plan->codeAdditional->classifier->scheme,
                'description' => $this->plan->codeAdditional->description,
                'id' => $this->plan->codeAdditional->code
            ];
        }

        if (!empty($this->plan->code_kekv_id)) {
            $this->data['additionalClassifications'][] = [
                "scheme" => $this->plan->codeKekv->classifier->scheme,
                'description' => $this->plan->codeKekv->description,
                'id' => $this->plan->codeKekv->code
            ];
        }

        $this->data['budget']['description'] = $this->plan->description;
        $this->data['budget']['notes'] = $this->plan->notes;
        $this->data['budget']['id'] = $this->plan->id;
    }


    protected function _setItems()
    {

        $items = $this->plan->items;
        foreach ($items as $item) {
            $itemData = [];
            $itemData['unit'] = [
                'code' => $item->unit->code,
                'name' => $item->unit->description,
            ];
            $itemData['description'] = $item->description;

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

            $itemData['quantity'] = $item->quantity;
            $this->data['items'][] = $itemData;
        }
    }



    protected function _setValue()
    {
        $this->data['budget'] += [
            'currency' => $this->plan->currency->currency_code,
            'amount' => $this->plan->amount
        ];
    }

    protected function _setProcuringEntity()
    {
        $organization = $this->plan->organization;
        $this->data['procuringEntity'] = [
            'identifier' => [
                'scheme' => "UA-EDR",
                'id' => $organization->identifier,
                'legalName' => $organization->name
            ],
            'name' => $organization->name,
        ];
    }

    public function getData()
    {
        $this->_setData();
        $this->_setItems();

        $this->_setValue();
        $this->_setProcuringEntity();

        return $this->data;
    }
}