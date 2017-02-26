<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ContractDocuments extends Model
{
    protected $fillable = [
        'contract_id',
        'title',
        'orig_id',
        'document_of',
        'change_id',
        'url',
        'format',
        'path',
        'date_published',
        'date_modified',
        'type_id'
    ];

    public function contract()
    {
        return $this->belongsTo('\App\Model\Contract', 'contract_id', 'id');
    }

    public function type()
    {
        return $this->hasOne('App\Model\DocumentType', 'id', 'type_id');
    }

    public function docuemnts()
    {
        return $this->morphTo();
    }
}
