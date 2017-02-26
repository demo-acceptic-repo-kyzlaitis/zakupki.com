<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    public $timestamps = false;

    /**
     * Scope a query to only include active languages.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function contacts()
    {
        return $this->belongsTo('\App\Model\Contacts');
    }
}
