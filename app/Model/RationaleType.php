<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RationaleType extends Model
{
    public $timestamps = false;

    protected $fillable =[
        'description',
        'title',
        'name',
    ];
}
