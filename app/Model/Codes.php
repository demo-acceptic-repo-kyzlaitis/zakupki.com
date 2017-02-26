<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Codes extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'description',
        'code',
        'parent_id',
        'type',
    ];

    public function classification() {
        return $this->hasMany('\App\Model\TenderClassification');
    }

    public function currency() {
        return $this->hasOne('\App\Model\Currencies', 'id', 'currency_id');
    }

    public function statusDesc() {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'tender');
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function tender() {
        return $this->belongsToMany('\App\Model\Tender');
    }

    public function classifier() {
        return $this->hasOne('\App\Model\Classifiers', 'id', 'type');
    }

    public function planning() {
        return $this->belongsToMany('\App\Model\Planning');
    }

    public function planningItem() {
        return $this->belongsToMany('\App\Model\Planning');
    }
    public function procedureTypeDesc() {
        $type_id = $this->type_id;
        $procedure = ProcedureTypes::where('id',$type_id)->first();
        return $procedure ? $procedure->procedure_name : '';
    }

    public function agents() {
        return $this->belongsToMany('\App\Model\Agent', 'agent_code', 'code_id', 'agent_id');
    }
}
