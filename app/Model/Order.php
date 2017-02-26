<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'number',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i');
    }

    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }




}
