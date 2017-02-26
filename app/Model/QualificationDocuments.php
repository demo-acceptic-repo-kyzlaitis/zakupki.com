<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class QualificationDocuments extends Model
{
    protected $fillable = [
        'qualification_id',
        'type_id',
        'title',
        'path',
        'orig_id',
        'format',
        'url',
        'date_published',
        'date_modified',
    ];

    public function qualification()
    {
        return $this->belongsTo('\App\Model\Qualification', 'qualification_id', 'id');
    }

    public function documentTypes()
    {
        return $this->belongsTo('App\Model\DocumentType', 'type_id', 'id');
    }
}
