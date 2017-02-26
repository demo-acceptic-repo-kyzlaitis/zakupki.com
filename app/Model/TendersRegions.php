<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TendersRegions extends Model
{
    public $timestamps = false;

    /**
     * Scope a query to only include active regions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
