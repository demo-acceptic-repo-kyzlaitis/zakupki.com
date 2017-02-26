<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FeatureValue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'title',
        'value',
        'feature_id'
    ];

    public $timestamps = false;

    public function feature()
    {
        return $this->belongsTo('\App\Model\Feature');
    }
    public function bids() {
        return $this->belongsToMany('\App\Model\Bid');
    }

//    public function setValueAttribute($value) {
//        $this->attributes['value'] = round($value * 100);
//    }
//
//    public function getValueAttribute($value) {
//        return number_format($value / 100, 2, '.', '');
//    }
}
