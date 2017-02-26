<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    protected $table = 'country';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_iso',
        'country_iso3',
        'country_iso_num',
        'country_fips',
        'country_alt_name',
        'country_name_ua',
        'currency',
        'country_phone_code',
        'country_status'
    ];

    public function identifiers()
    {
        return $this->hasMany('\App\Model\Identifier', 'country_iso', 'country_iso');
    }

    public function scopeActive($query)
    {
        return $query->where('country_status', 1);
    }
}
