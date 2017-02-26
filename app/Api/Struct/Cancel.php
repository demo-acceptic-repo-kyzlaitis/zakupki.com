<?php

namespace App\Api\Struct;

use App\Model\Cancellation as CancellationModel;
use Carbon\Carbon;

class Cancel extends Structure
{
    protected $tender;
    protected $data = [];

    public function __construct(CancellationModel $cancel)
    {
        $this->cancel = $cancel;
        $tender = $cancel->cancelable->type == 'tender' ? $cancel->cancelable : $cancel->cancelable->tender;
        $this->uri = '/tenders/'.$tender->cbd_id.'/cancellations';
        if (!empty($cancel->cbd_id)) {
            $this->uri .= '/'.$cancel->cbd_id;
        }
        $this->uri .= '?acc_token='.$tender->access_token;
    }


    public function getData()
    {
        $this->data = [
            'cancellationOf' => $this->cancel->cancelable->type,
            'status' => $this->cancel->status,
            'reason' => $this->cancel->reason,
            'date' => date('Y-m-d H:i:s', strtotime($this->cancel->created_at))
        ];
        if ($this->cancel->cancelable->type == 'lot') {
            $this->data['relatedLot'] = $this->cancel->cancelable->cbd_id;
        }

        return $this->data;
    }
}