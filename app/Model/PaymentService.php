<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PaymentService extends Model
{
    public function payments()
    {
        return $this->hasMany('App\Model\Payments');
    }
}
