<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'postal_code',
        'region_id'
    ];

    public $timestamps = false;
}
