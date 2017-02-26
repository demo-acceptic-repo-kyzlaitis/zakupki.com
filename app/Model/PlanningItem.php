<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlanningItem extends Model
{
    public function planning()
    {
        return $this->belongsTo('\App\Model\Planning');
    }

    public function unit()
    {
        return $this->hasOne('\App\Model\Units', 'id', 'unit_id');
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
