<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    public $type = 'award';

    protected $fillable = [
        'status',
        'cbd_id',
        'bid_id',
        'amount',
        'bid_id',
        'tender_id',
        'tax_included',
        'organization_id',
        'qualified',
        'subcontracting_details',
        'currency_id',
        'complaint_date_start',
        'complaint_date_end',
        'date', //дата когда был акцеп еворда
    ];


    public function bid()
    {
        return $this->belongsTo('\App\Model\Bid');
    }

    public function documents()
    {
        return $this->hasMany('\App\Model\AwardDocuments');
    }

    public function status()
    {
        return $this->belongsTo('\App\Model\Status');
    }

    public function statusDesc()
    {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'award');
    }

    public function document()
    {
        return $this->belongsTo('\App\Model\Document');
    }

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    public function item()
    {
        return $this->belongsTo('\App\Model\Item');
    }

    public function complaint()
    {
        return $this->morphOne('\App\Model\Complaint', 'complaintable');
    }

    public function complaints()
    {
        return $this->morphMany('\App\Model\Complaint', 'complaintable');
    }


    public function contract()
    {
        return $this->hasOne('\App\Model\Contract');
    }

    public function contracts()
    {
        return $this->hasMany('\App\Model\Contract');
    }

    public function currency()
    {
        return $this->hasOne('\App\Model\Currencies', 'id', 'currency_id');
    }

    public function tender()
    {
        return $this->belongsTo('App\Model\Tender');
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = round($value * 100);
    }

    public function getAmountAttribute($value)
    {
        return number_format($value / 100, 2, '.', '');
    }

    public function canComplaint()
    {
        if ($this->tender->type_id == 5){
           if($this->status != 'active'){
               return false;
           }
        }
        if (strtotime($this->complaint_date_start) <= time() &&
            (time() <= strtotime($this->complaint_date_end) || is_null($this->complaint_date_end)) &&
            !$this->tender->isOwner()) {

            return true;
        } else {
            return false;
        }
    }

    public function canComplaintAnswer()
    {
        if (strtotime($this->complaint_date_start) <= time() && (time() <= strtotime($this->complaint_date_end) || is_null($this->complaint_date_end)) && $this->tender->isOwner())
            return true;
        else
            return false;
    }
}