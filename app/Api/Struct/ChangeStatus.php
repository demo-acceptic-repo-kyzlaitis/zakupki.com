<?php

namespace App\Api\Struct;

use Carbon\Carbon;

class ChangeStatus extends Structure
{
    public $tender;
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
        $className = get_class($entity);
        switch ($className) {

            case 'App\Model\Tender':
                $this->tender = $entity;
                $this->uri = '/tenders';
                if (!empty($entity->cbd_id)) {
                    $this->uri .= '/'.$entity->cbd_id.'?acc_token='.$entity->access_token;
                }
                break;
            case 'App\Model\Bid':
                $this->tender = $entity->bidable->tender;
                $this->uri = '/tenders/'.$this->tender->cbd_id.'/bids';
                if (!empty($entity->cbd_id)) {
                    $this->uri .= '/' . $entity->cbd_id . '?acc_token=' . $entity->access_token;
                }
                break;
            case 'App\Model\Qualification':
                $this->tender = $entity->bid->tender;
                $this->uri = '/tenders/' . $this->tender->cbd_id . '/qualifications';
                if (!empty($entity->cbd_id)) {
                    $this->uri .= '/' . $entity->cbd_id . '?acc_token=' . $this->tender->access_token;
                }
                break;
            case 'App\Model\Award':
                $this->tender = $entity->tender;
                $this->uri = '/tenders/'.$this->tender->cbd_id.'/awards';
                if (!empty($entity->cbd_id)) {
                    $this->uri .= '/'.$entity->cbd_id;
                }
                $this->uri .= '?acc_token='.$this->tender->access_token;
                break;
        }
    }


    public function getData()
    {
        return ['status' => $this->entity->status];
    }
}