<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TenderErrors extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tender_id',
        'hash',
        'message',
    ];

    public function description()
    {
        return $this->hasOne('\App\Model\Errors', 'hash', 'hash');
    }
}
