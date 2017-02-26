<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model {
    protected $fillable = [
        'namespace',
        'document_type',
        'lang_ua',
        'lang_ru',
    ];

    public function documents(){
        return $this->belongsToMany('App\Model\Document')->withTimestamps();
    }

    public function bidDocuments()
    {
        return $this->belongsToMany('App\Model\BidDocuments')->withTimestamps();
    }
}
