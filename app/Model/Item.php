<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    public $type = 'item';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'description_en',
        'classification',
        'unit_id',
        'same_delivery_address',
        'quantity',
        'delivery_address',
        'country_id',
        'region_id',
        'region_name',
        'postal_code',
        'locality',
        'delivery_date_start',
        'delivery_date_end',
        'cbd_id',
        'tender_id',
        'lot_id'
    ];


    public static $validation = [
        'description'           => '',
        'classification'        => '',
        'unit_id'               => '',
        'same_delivery_address' => '',
        'quantity'              => '',
        'delivery_address'      => '',
        'country_id'            => '',
        'region_id'             => '',
        'postal_code'           => '',
        'locality'              => '',
        'delivery_date_start'   => '',
        'delivery_date_end'     => '',
        'cbd_id'                => '',
        'tender_id'             => '',
    ];

    public static $messages = [
        'description',
        'classification',
        'unit_id',
        'same_delivery_address',
        'quantity',
        'delivery_address',
        'country_id',
        'region_id',
        'postal_code',
        'locality',
        'delivery_date_start',
        'delivery_date_end',
        'cbd_id',
        'tender_id'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'delivery_date_start',
        'delivery_date_end'
    ];

    public function awards(){
        return $this->hasMany('\Model\Award');
    }

    public function setDeliveryDateStartAttribute($value) {
        $this->attributes['delivery_date_start'] = (!is_null($value)) ? Carbon::parse($value) : null;
    }

    public function setDeliveryDateEndAttribute($value) {
        if (!is_null($value)) {
            $this->attributes['delivery_date_end'] = Carbon::parse($value);
        }
    }

    public function getDeliveryDateStartAttribute($date) {
        if (!is_null($date)) {
            return Carbon::parse($date)->format('d.m.Y H:m');
        }
    }

    public function getDeliveryDateEndAttribute($date) {
        if (!is_null($date)) {
            return Carbon::parse($date)->format('d.m.Y H:m');
        }
    }

    public function unit()
    {
        return $this->hasOne('\App\Model\Units', 'id', 'unit_id');
    }

    public function codes()
    {
        return $this->belongsToMany('\App\Model\Codes');
    }

    public function region()
    {
        return $this->hasOne('\App\Model\TendersRegions', 'id', 'region_id');
    }

    public function lot()
    {
        return $this->belongsTo('\App\Model\Lot');
    }

    public function documents() {
        return $this->morphMany('\App\Model\Document', 'documentable');
    }

    public function questions() {
        return $this->morphMany('\App\Model\Question', 'questionable');
    }

    public function features() {
        return $this->morphMany('\App\Model\Feature', 'featureable');
    }

    public function tender()
    {
        return ($this->lot) ? $this->lot->tender() : null;
    }

    public function getAddress()
    {
        $address = [];
        if (!empty($this->postal_code)) $address[] = '<span class="item-deliveryAddress.postalCode">'.$this->postal_code.'</span>';
        $address[] = '<span class="item-deliveryAddress.countryName">Україна</span>';
        if (!empty($this->region_name)) $address[] = '<span class="item-deliveryAddress.region">'.$this->region_name.'</span>';
        if (!empty($this->locality)) $address[] = '<span class="item-deliveryAddress.locality">'.$this->locality.'</span>';
        if (!empty($this->delivery_address)) $address[] = '<span class="item-deliveryAddress.streetAddress">'.$this->delivery_address.'</span>';

        return implode(', ', $address);
    }

    public function canQuestion()
    {
        return $this->lot->canQuestion();
    }

}
