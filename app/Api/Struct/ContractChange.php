<?php

namespace App\Api\Struct;

use App\Model\ContractChange as ContractChangeModel;
use Carbon\Carbon;

class ContractChange extends Structure
{
    public $change;
    public $data = [];

    public function __construct(ContractChangeModel $change)
    {
        $this->change = $change;
        if (!empty($this->change->cbd_id)) {
            $this->uri = '/contracts/'.$change->contract->cbd_id.'/changes/'.$change->cbd_id.'?acc_token='.$change->contract->access_token;
        } else {
            $this->uri = '/contracts/'.$change->contract->cbd_id.'/changes?acc_token='.$change->contract->access_token;
        }
        
    }


    public function getData()
    {
        if ($this->change->status == 'activate') {
            $this->change->status = 'active';
            $this->change->save();
        }
        
        $this->data = [
            'contractNumber' => $this->change->contract_number,
            'rationale' => $this->change->rationale,
            'rationaleTypes' => [$this->change->rationaleType->name],
            'status' => $this->change->status,
            'dateSigned' => Carbon::parse($this->change->date_signed)->toIso8601String(),
        ];
        
        if (!empty($this->change->cbd_id)) {
            $this->data['id'] = $this->change->cbd_id;
        }

        return $this->data;
    }
}