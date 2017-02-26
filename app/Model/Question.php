<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'cbd_id',
        'organization_id',
        'title',
        'description',
        'organization_to_id',
        'answer',
        'date_answer',
        'created_at',
        'tender_id'
    ];

    protected $dates = ['created_at'];

    public function questionable()
    {
        return $this->morphTo();
    }

    public function setCreatedAtAttribute($value) {
        $this->attributes['created_at'] = Carbon::parse($value);
    }

    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender');
    }

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    /**
     * Scope a query to not answered questions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotAnswered($query)
    {
        return $query->where('answer', '=', '');
    }




}
