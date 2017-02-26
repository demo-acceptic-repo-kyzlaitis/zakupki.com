<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Identifier extends Model
{

    protected $table = 'identifiers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_iso',
        'scheme',
        'name',
        'description',
        'uri',
        'status'
    ];

    public function country()
    {
        return $this->belongsTo('\App\Model\Country', 'country_iso', 'country_iso');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function organisations() {
        return $this->belongsToMany('App\Model\Organization', 'identifier_organisation', 'identifier_id', 'organisation_id');
    }
}
