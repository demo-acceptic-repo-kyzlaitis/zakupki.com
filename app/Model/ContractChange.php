<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ContractChange extends Model
{
    protected $fillable =[
        'cbd_id',
        'tender_id',
        'contract_number',
        'rationale',
        'rationale_type_id',
        'date',
        'date_signed',
        'status'
    ];

    public function setDateAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['date'] = Carbon::parse($value);
        }
    }

    public function getDateAttribute($date) {
        if (is_null($date)) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y');
    }

    public function setDateSignedAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['date_signed'] = Carbon::parse($value);
        }
    }

    public function getDateSignedAttribute($date) {
        if (is_null($date)) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function rationaleType()
    {
        return $this->belongsTo('\App\Model\RationaleType');
    }
    
    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender');
    }
    
    public function contract()
    {
        return $this->belongsTo('\App\Model\Contract');
    }

    public function contractDocuments() {
        return $this->morphMany('\App\Model\ContractDocuments', 'documentable');
    }
}
