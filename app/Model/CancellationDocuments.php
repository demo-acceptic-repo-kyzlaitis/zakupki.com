<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CancellationDocuments extends Model
{
    protected $fillable = [
        'cancel_id',
        'title',
        'orig_id',
        'url',
        'path',
        'date_published',
        'date_modified',
    ];

    public function cancel()
    {
        return $this->belongsTo('\App\Model\Cancellation', 'cancellation_id', 'id');
    }

    /*Date Getters*/
    public function getUpdatedAtAttribute($date) {
        return Carbon::parse($date)->format('d.m.Y H:i');
    }

}
