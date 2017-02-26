<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{

    public static $status =[
        'pending'   => 'pending', // только создан и еще не прошел модерацию
        'suspended' => 'suspended', // приостановлен в связи обновлением
        'active'    => 'active', // активный
        'stopped'   => 'stopped', // остановлен и больше юзать не нужно
    ];

    protected $fillable = [
        'field',
        'start_amount',
        'end_amount',
        'comment',
        'organization_id',
        'status',
        'region_id',
        'tender_statuses',
        'kinds',
        'regions',
        'procedure_types',
        'guarantee', // 1 - да 0 - нет | default 0
        'email_newsletter', // розсилка на почту 1 - да 0 - нет  | default 0
        'email_frequency' //weekly daily | default null
    ];

    public function organization() {
        return $this->belongsTo('App\Model\Organization');
    }

    public function codes() {
        return $this->belongsToMany('\App\Model\Codes', 'agent_code', 'agent_id', 'code_id');
    }

    public function region() {
        return $this->hasOne('App\Model\TendersRegions');
    }

    public function agentHistory() {
        return $this->hasMany('App\Model\AgentHistory');
    }

    public function setStartAmountAttribute($value) {
        $this->attributes['start_amount'] = round($value * 100);
    }

    public function getStartAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function setEndAmountAttribute($value) {
        $this->attributes['end_amount'] = round($value * 100);
    }

    public function getEndAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function getKindsAttribute($value) {
        return explode('|', $value);
    }

    public function getTenderStatusesAttribute($value) {
        return explode('|', $value);
    }

    public function getRegionsAttribute($value) {
        return explode('|', $value);
    }

    public function getProcedureTypesAttribute($value) {
        return explode('|', $value);
    }
}
