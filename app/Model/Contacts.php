<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    protected $fillable = [
        'contact_name',
        'contact_name_en',
        'contact_phone',
        'contact_email',
        'contact_fax',
        'contact_url',
        'contact_available_lang',
        'organization_id',
        'primary'
    ];

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    public function language()
    {
        return $this->hasOne('\App\Model\Languages');
    }
//    public function tenderContacts()
//    {
//        return $this->belongsTo('\App\Model\TenderContact');
//    }
    public function tenderContacts(){
        return $this->hasMany('\App\Model\TenderContact');
    }


    public function contactTenders()
    {
        return $this->hasMany('\App\Model\TenderContacts', 'contact_id', 'id');
    }
}

