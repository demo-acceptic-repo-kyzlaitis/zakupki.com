<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AgentHistory extends Model
{

    protected $table = 'agent_history';

    protected $fillable = [
        'organization_id', //какой организации пренадлежит история
        'tender_id', // какой тендре был тогда найдет по критерия агента
        'agent_id' // история агента 
    ];


    public function agent() {
        return $this->belongsTo('App\Model\Agent');
    }

    public function tender() {
        return $this->belongsTo('App\Model\Tender');
    }



}
