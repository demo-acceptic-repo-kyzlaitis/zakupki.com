<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    public function planningItem()
    {
        return $this->hasMany('\App\Model\PlanningItem');
    }

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    public function dkppCode()
    {
        return $this->belongsTo('\App\Model\Codes', 'dkpp', 'id');
    }

    public function cpvCode()
    {
        return $this->belongsTo('\App\Model\Codes', 'cpv', 'id');
    }
    public function codes()
    {
        return $this->belongsToMany('\App\Model\Codes');
    }



}
