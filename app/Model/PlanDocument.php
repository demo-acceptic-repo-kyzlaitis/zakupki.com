<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlanDocument extends Model
{
    protected $fillable = [
        'plan_id',
        'title',
        'path',
        'orig_id',
        'format',
        'url',
        'date_published',
        'date_modified',
    ];

    public function plan()
    {
        return $this->belongsTo('\App\Model\Plan', 'plan_id', 'id');
    }

    public function getFileName() {
        return $this->title;
    }
}
