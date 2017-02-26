<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ComplaintDocument extends Model
{
    protected $fillable = [
        'orig_id',
        'type_id',
        'organization_id',
        'document_parent_id',
        'title',
        'description',
        'format',
        'url',
        'path',
        'author'
    ];

    public function getCreatedAtAttribute($date) {
        return Carbon::parse($date)->format('d.m.y H:i');
    }

    public function complaint()
    {
        return $this->belongsTo('\App\Model\Complaint');
    }

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    public function scopeComplainter($query)
    {
        return $query->where('author', 'complaint_owner');
    }

    public function scopeTenderer($query)
    {
        return $query->where('author', 'tender_owner');
    }

}
