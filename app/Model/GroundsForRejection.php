<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroundsForRejection extends Model
{
    protected $table = 'grounds_for_rejection';

    /**
     * Scope a query to only include active languages.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrequalification($query)
    {
        return $query->where('namespace', 'pre-qualification')
            ->where('bid_status', 'participant')
            ->where('active', 1);
    }
}
