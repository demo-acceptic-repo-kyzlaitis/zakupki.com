<?php

namespace App\Api\Struct;

use App\Model\Lot as LotModel;

class Lot extends Structure
{
    protected $tender;
    protected $data = [];

    public function __construct(LotModel $lot)
    {
        $this->lot = $lot;
        if (!empty($lot->cbd_id)) {
            $this->uri = '/tenders/'.$lot->tender->cbd_id.'/lots/'.$lot->cbd_id.'?acc_token='.$lot->tender->access_token;
        } else {
            $this->uri = '/tenders/'.$lot->tender->cbd_id.'/lots?acc_token='.$lot->tender->access_token;
        }
    }

    protected function _setData()
    {
        $this->data['title'] = $this->lot->title;
        $this->data['description'] = $this->lot->description;
        if (!empty($this->lot->cbd_id)) {
            $this->data['id'] = $this->lot->cbd_id;
        }

        if ($this->lot->tender->procedureType->procurementMethodType == 'aboveThresholdEU') {
            $this->data['title_en'] = $this->lot->title_en;
        }
        if ($this->lot->tender->procedureType->procurementMethodType == 'aboveThresholdUA.defense' || $this->lot->tender->procedureType->procurementMethodType == 'competitiveDialogueEU') {
            $this->data['title_en'] = $this->lot->title_en;
            $this->data['description_en'] = $this->lot->description_en;
        }
    }

    protected function _setValue()
    {
        $this->data['value'] = [
            'amount' => $this->lot->amount,
            'valueAddedTaxIncluded' => $this->lot->tender->tax_included
        ];
    }

    protected function _setMinStep()
    {
        if($this->lot->tender->procedureType->procurement_method_type != 'negotiation.quick' &&
            $this->lot->tender->procedureType->procurement_method_type != 'negotiation') {

            $this->data['minimalStep'] = [
                'amount'                => $this->lot->minimal_step,
                'valueAddedTaxIncluded' => $this->lot->tender->tax_included,
            ];
        }
    }

    protected function  _setGuarantee() {
        $this->data['guarantee'] = [
            'amount' => $this->lot->guarantee_amount,
            'currency' => $this->lot->tender->currency->currency_code
        ];
    }

    public function getData()
    {
        if (empty($this->data)) {
            $this->_setData();
            $this->_setMinStep();
            $this->_setValue();
            if($this->lot->guarantee_amount !== null) {
                $this->_setGuarantee();
            }
        }

        return $this->data;
    }

}