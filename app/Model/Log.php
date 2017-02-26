<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $type = 'logs';

    protected $fillable = [
        'id',
        'stings',
        'updated_at',
        'created_at',
        'exist'
    ];
}
