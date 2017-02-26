<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    protected $fillable = [
        'orig_id',
        'type_id',
        'document_parent_id',
        'title',
        'description',
        'format',
        'url',
        'upload_url',
        'path',
        'tender_id',
        'date_published',
        'date_modified'
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function awards(){
        return $this->hasMany('App\Model\Award');
    }

    public function bids(){
        return $this->hasMany('App\Model\Bid');
    }

    public function cancellations(){
        return $this->hasMany('App\Model\Cancellation');
    }

    public function complaints(){
        return $this->hasMany('App\Model\Complaint');
    }

    public function contracts(){
        return $this->hasMany('App\Model\Contract');
    }

    public function tender(){
        return $this->belongsTo('App\Model\Tender');
    }

    public function documentTypes(){
        return $this->belongsToMany('App\Model\DocumentType')->withTimestamps();
    }

    public function type()
    {
        return $this->hasOne('App\Model\DocumentType', 'id', 'type_id');
    }

    /*Date Getters*/
    public function getUpdatedAtAttribute($date) {
        return Carbon::parse($date)->format('d.m.Y H:i');
    }
    public function getCreatedAtAttribute($date) {
        return Carbon::parse($date)->format('d.m.Y H:i');
    }
}
