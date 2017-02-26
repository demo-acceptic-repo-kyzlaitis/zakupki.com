<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    public $fillable = [
        'amount',
        'balance',
        'payment_service_id',
        'comment'
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo('App\Model\PaymentService', 'payment_service_id', 'id');
    }
    public function product()
    {
        return $this->hasOne('App\Model\Product', 'id','products_id');
    }

    public function payment()
    {
        return $this->morphTo();
    }

    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function setBalanceAttribute($value) {
        $this->attributes['balance'] = round($value * 100);
    }

    public function getBalanceAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function setDateTransactionAttribute($value) {
        if (!is_null($value)) {
            $this->attributes['date_transaction'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }
    }

    public function getDateTransactionAttribute($date) {
        if ($date == null) {

            return null;
        }
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }





}
