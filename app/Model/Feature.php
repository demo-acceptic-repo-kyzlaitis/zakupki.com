<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cbd_id',
        'entity_type_id',
        'entity_id',
        'title',
        'title_en',
        'description',
        'tender_id'
    ];

    public function featureable()
    {
        return $this->morphTo();
    }

    public function values() {
        return $this->hasMany('\App\Model\FeatureValue');
    }

    public function getAmount()
    {
        $amount = array();
        foreach ($this->values as $value) {
            $amount[] = $value->value;
        }
        return max($amount);
    }

}
