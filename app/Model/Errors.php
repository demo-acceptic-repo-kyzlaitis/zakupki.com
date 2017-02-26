<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Errors extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hash',
        'text',
    ];

}
