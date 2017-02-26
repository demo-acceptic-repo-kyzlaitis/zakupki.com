<?php

namespace App\Api\Struct;

use App\Model\Tender as TenderModel;
use Carbon\Carbon;

class TenderUpdate extends Structure
{
    protected $tender;
    protected $arFields;
    protected $data = [];

    public function __construct(TenderModel $tender, $arFields = [])
    {
        $this->tender = $tender;
        $this->arFields = $arFields;
        $this->uri = '/tenders';
        if (!empty($tender->cbd_id)) {
            $this->uri .= '/'.$tender->cbd_id.'?acc_token='.$tender->access_token;
        }
    }

    public function getData()
    {
        foreach ($this->arFields as $field) {
            $this->data[$field] = $this->tender->$field;
        }

        return $this->data;
    }
}