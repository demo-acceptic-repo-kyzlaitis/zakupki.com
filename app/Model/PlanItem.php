<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlanItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plan_id',
        'description',
        'quantity',
        'unit_id',
    ];

    public function unit()
    {
        return $this->hasOne('\App\Model\Units', 'id', 'unit_id');
    }

    public function codes()
    {
        return $this->belongsToMany('\App\Model\Codes');
    }

    public function plan()
    {
        return $this->belongsTo('App\Model\Plan');
    }
}
