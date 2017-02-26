<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AwardDocuments extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'award_id',
        'title',
        'cbd_id',
        'orig_id',
        'format',
        'url',
        'path',
        'date_published',
        'date_modified',
    ];

    public function award()
    {
        return $this->belongsTo('\App\Model\Award', 'award_id', 'id');
    }

    public function getCreatedAtAttribute($value) {
        if ($value == null || $value === '') {
            return null;
        }
        return Carbon::parse($value)->setTimezone('Europe/Kiev')->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value) {
        if ($value == null || $value === '') {
            return null;
        }
        return Carbon::parse($value)->setTimezone('Europe/Kiev')->format('Y-m-d H:i:s');
    }
}
