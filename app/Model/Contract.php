<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable =[
        'award_id',
        'tender_id',
        'title',
        'contractID',
        'contract_number',
        'description',
        'date_signed',
        'period_date_start',
        'period_date_end',
        'amount',
        'cbd_id',
        'status',
        'access_token',
        'amount',
        'amount_paid',
        'termination_details',
        'date',
        'signed',
//        'amount_per_item', todo comment out for amount per item task author illia
    ];

    public function contractDocuments() {
        $this->morphMany('App\Model\ContractDocuments', 'documentable');
    }
    
    public function status(){
        return $this->belongsTo('\App\Model\Status');
    }

    public function statusDesc() {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'contract');
    }

    public function documents(){
        return $this->HasMany('\App\Model\ContractDocuments');
    }

    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender');
    }
    
    public function changes()
    {
        return $this->hasMany('\App\Model\ContractChange');
    }

    public function change()
    {
        return $this->hasOne('\App\Model\ContractChange')->where('status', '!=', 'active');
    }
    
    public function award()
    {
        return $this->belongsTo('\App\Model\Award');
    }

    public function canEdit($organizationId)
    {
        return ($this->status == 'pending' && $this->tender->organization->id == $organizationId) ? true : false;
    }

    public function setDateSignedAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['date_signed'] = Carbon::parse($value);
        }
    }

    public function setPeriodDateStartAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['period_date_start'] = Carbon::parse($value);
        }
    }

    public function setPeriodDateEndAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['period_date_end'] = Carbon::parse($value);
        }
    }

    /*Date Getters*/
    public function getDateSignedAttribute($date) {
        if (is_null($date)) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y H:i');
    }

    public function getPeriodDateStartAttribute($date) {
        if (is_null($date)) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y');
    }

    public function getPeriodDateEndAttribute($date) {
        if (is_null($date)) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y');
    }

    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function setAmountPaidAttribute($value) {
        $this->attributes['amount_paid'] = round($value * 100);
    }

    public function getAmountPaidAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }
}
