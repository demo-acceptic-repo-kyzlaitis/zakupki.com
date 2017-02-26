<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function transaction()
    {
        return $this->belongsTo('App\Model\Transaction');
    }
}
