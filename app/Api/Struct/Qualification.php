<?php

namespace App\Api\Struct;

use App\Model\Qualification as QualificationModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Qualification extends Structure
{
    protected $tender;
    protected $qualification;
    protected $data = [];

    public function __construct(QualificationModel $qualification)
    {
        $this->qualification = $qualification;
        $tender = $qualification->bid->tender;
        $this->uri = '/tenders/' . $tender->cbd_id . '/qualifications';
        if (!empty($qualification->cbd_id)) {
            $this->uri .= '/' . $qualification->cbd_id . '?acc_token=' . $tender->access_token;
        }
    }

    public function getData()
    {
        $this->data['status'] = $this->qualification->status;
        if ($this->data['status'] == 'pending') {
            $this->data['eligible'] = $this->qualification->eligible;
            $this->data['qualified'] = $this->qualification->qualified;
        }

        return $this->data;
    }
}