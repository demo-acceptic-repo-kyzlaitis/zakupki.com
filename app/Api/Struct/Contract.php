<?php

namespace App\Api\Struct;

use App\Model\Contract as ContractModel;
use Carbon\Carbon;

class Contract extends Structure
{
    public $tender;
    public $contract;
    public $data = [];

    public function __construct(ContractModel $contract)
    {
        $this->contract = $contract;
        $tender = $contract->tender;
        if ($contract->access_token != '') {
            $this->uri = '/contracts/'.$contract->cbd_id.'?acc_token='.$contract->access_token;
        } else {
            $this->uri = '/tenders/'.$tender->cbd_id.'/contracts';
            if (!empty($contract->cbd_id)) {
                $this->uri .= '/'.$contract->cbd_id;
            }
            $this->uri .= '?acc_token='.$tender->access_token;
        }

    }


    public function getData()
    {
        $this->data = [
            'contractNumber' => $this->contract->contract_number,
            'period' => [
                'startDate' => Carbon::parse($this->contract->period_date_start)->toIso8601String(),
                'endDate' => Carbon::parse($this->contract->period_date_end)->toIso8601String()
            ],
            'dateSigned' => Carbon::parse($this->contract->date_signed)->toIso8601String(),
            'value' => [
                'amount' => $this->contract->amount
            ],
            'status' => $this->contract->status,
        ];
        if ($this->contract->amount_paid > 0) {
            $this->data['amountPaid']['amount'] = $this->contract->amount_paid;
            $this->data['terminationDetails'] = $this->contract->termination_details;
        }

        return $this->data;
    }
}