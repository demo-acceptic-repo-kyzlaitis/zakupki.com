<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Qualification extends Model
{
    public $type = 'qualification';
    protected $fillable = [
        'status',
        'cbd_id',
        'bid_id',
        'lot_id',
        'tender_id',
        'eligible',
        'qualified',
        'unsuccessful_title',
        'unsuccessful_description'
    ];

    public function status()
    {
        return $this->belongsTo('\App\Model\Status');
    }

    public function lot()
    {
        return $this->belongsTo('\App\Model\Qualification', 'lot_id', 'id');
    }

    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender');
    }

    public function documents()
    {
        return $this->hasMany('\App\Model\QualificationDocuments');
    }

    public function bid()
    {
        return $this->belongsTo('\App\Model\Bid');
    }

    public function statusDesc()
    {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'qualification');
    }

    public function canComplaint()
    {
        $tender = $this->bid->tender;
        if (is_object($tender->procedureType) && ($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                $tender->procedureType->procurement_method_type == 'competitiveDialogueUA' ||
                $tender->procedureType->procurement_method_type == 'competitiveDialogueEU' ||
                $tender->procedureType->procurement_method == 'selective'))
            return (Auth::check() && ($tender->status == 'active.pre-qualification.stand-still') && !$tender->isOwner()) ? true : false;
        else
            return false;
    }

    public function complaint()
    {
        return $this->morphOne('\App\Model\Complaint', 'complaintable');
    }

    public function complaints()
    {
        return $this->morphMany('\App\Model\Complaint', 'complaintable');
    }
}
