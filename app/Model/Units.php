<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Units extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'symbol',
        'description',
    ];
}
