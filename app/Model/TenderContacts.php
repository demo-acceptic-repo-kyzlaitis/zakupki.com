<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TenderContacts extends Model
{
    public $timestamps = false;

    protected $fillable =[
        'tender_id',
        'contact_id'
    ];

    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender', 'tender_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo('\App\Model\Contacts', 'contact_id', 'id');
    }
}
