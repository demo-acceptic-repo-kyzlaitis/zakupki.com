<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RejectReason extends Model
{
    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOpen($query)
    {
        return $query->where('procurement_method_type', 'open');
    }

    public function scopeDefense($query)
    {
        return $query->where('procurement_method_type', 'defense');
    }

}
