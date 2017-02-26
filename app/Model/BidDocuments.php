<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BidDocuments extends Model
{
    protected $fillable = [
        'bid_id',
        'type_id',
        'title',
        'path',
        'orig_id',
        'format',
        'confidential',
        'confidential_cause',
        'description_decision',
        'url',
        'date_published',
        'date_modified',
    ];

    public function bid()
    {
        return $this->belongsTo('\App\Model\Bid', 'bid_id', 'id');
    }

    public function documentTypes()
    {
        return $this->belongsToMany('App\Model\DocumentType')->withTimestamps();
    }
    
    public function type()
    {
        return $this->hasOne('App\Model\DocumentType', 'id', 'type_id')->where('namespace', 'bid');
    }
}
