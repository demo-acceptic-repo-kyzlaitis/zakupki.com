<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TenderContact extends Model
{
    public $type = 'tender_contacts';
    public $timestamps = false;

    public function contact()
    {
        return $this->belongsTo('\App\Model\Contacts');
    }

}
