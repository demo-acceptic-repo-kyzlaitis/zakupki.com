<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lot extends Model
{

    public $type = 'lot';
    public $entityName = 'lot';

    public static $validation = [

    ];

    public static $messages = [

    ];



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cbd_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'amount',
        'guarantee_amount',
        'guarantee_currency_id',
        'currency_id',
        'tax_included',
        'minimal_step',
        'auction_start_date',
        'auction_end_date',
        'auction_url',
        'status',
        'date',
    ];

    public function setAuctionStartDateAttribute($value) {
        $this->attributes['auction_start_date'] = null;
        if (!empty($value)) {
            $this->attributes['auction_start_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }
    }

    public function setAuctionEndDateAttribute($value) {
        $this->attributes['auction_end_date'] = null;
        if (!empty($value)) {
            $this->attributes['auction_end_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }
    }

    public function getAuctionStartDateAttribute($date) {
        if ($date == null || empty($date)) {

            return null;
        }
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getAuctionEndDateAttribute($date) {
        if ($date == null || empty($date)) {

            return null;
        }
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function payments() {
        return $this->morphMany('\App\Model\Transaction', 'payment');
    }

    public function tender()
    {
        return $this->belongsTo('App\Model\Tender');
    }

    public function items()
    {
        return $this->hasMany('App\Model\Item');
    }

    public function bid()
    {
        return $this->belongsTo('App\Model\Bid');
    }

    public function documents() {
        return $this->morphMany('\App\Model\Document', 'documentable');
    }

    public function questions() {
        return $this->morphMany('\App\Model\Question', 'questionable');
    }

    public function features() {
        return $this->morphMany('\App\Model\Feature', 'featureable');
    }

    public function cancel() {
        return $this->morphOne('\App\Model\Cancellation', 'cancelable');
    }

    public function bids() {
        return $this->morphMany('\App\Model\Bid', 'bidable');
    }

    public function currency() {
        return $this->hasOne('\App\Model\Currencies', 'id', 'currency_id');
    }

    public function guaranteeCurrency() {
        return $this->hasOne('\App\Model\Currencies', 'id', 'guarantee_currency_id');
    }

    public function statusDesc() {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'lot');
    }

    public function canQuestion()
    {
        if ($this->status != 'active')
            return false;

        if ($this->tender->procedureType->threshold_type == 'below') {
            return ($this->tender->status == 'active.enquiries' && strtotime($this->tender->enquiry_end_date) > time()) ? true : false;
        } else {
            if ($this->tender->status == 'active.tendering' && strtotime($this->tender->enquiry_end_date) > time()) {
                if ($this->tender->procedureType->procurement_method == 'selective' && isset($this->tender->stages)) {
                    if ($this->tender->stages->firstStage->allBids()->where('status', 'active')->where('organization_id', Auth::user()->organization->id)->first()) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }


    public function canComplaint()
    {
        if ($this->tender->procedureType->threshold_type == 'below' || $this->tender->procedureType->procurement_method == 'selective') {
            return $this->canBid() || $this->canQuestion();
        } else {
            return time() < strtotime($this->tender->complaint_date_end);
        }
    }

    public function canCancel()
    {
        return ($this->cbd_id != '' && $this->status == 'active') ? true : false;
    }

    public function canBid()
    {
        if ($this->status == 'active' && (($this->tender->status == 'active.tendering' && strtotime($this->tender->tender_end_date) > time()) /*|| $this->tender->status == 'active.qualification'*/)) {
            if ($this->tender->procedureType->procurement_method == 'selective' && isset($this->tender->stages)) {
                if ($this->tender->stages->firstStage->allBids()->where('status', 'active')->where('organization_id', Auth::user()->organization->id)->first()) {
                    return true;
                }
            } else {
                return true;
            }
        }
        if (is_object($this->tender->procedureType) && ($this->tender->procedureType->procurement_method == 'open' && $this->tender->procedureType->threshold_type == 'above'))
            if (Auth::check() && $this->tender->status == 'active.awarded') {
                $bid = $this->bids()->where('organization_id', Auth::user()->organization->id)->first();
                return ($bid && $bid->award && $bid->award->status == 'active') ? true : false;
            } elseif (Auth::check() && $this->tender->status == 'active.qualification') {
                $bid = $this->bids()->where('organization_id', Auth::user()->organization->id)->first();
                return ($bid && $bid->award && $bid->award->status == 'pending') ? true : false;
            } else {
                return (Auth::check() && $this->status == 'active' && ($this->tender->status == 'active.tendering' /*|| $this->tender->status == 'active.qualification'*/)) ? true : false;
            }
        elseif ($this->tender->procedureType && $this->tender->procedureType->procurement_method == 'open' && $this->tender->procedureType->threshold_type == 'below')
            if (Auth::check() && $this->tender->status == 'active.qualification') {
                $bid = $this->bids()->where('organization_id', Auth::user()->organization->id)->first();
                return ($bid && $bid->award && $bid->award->status == 'pending') ? true : false;
            } else {
                return (Auth::check() && $this->status == 'active' && ($this->tender->status == 'active.tendering' /*|| $this->tender->status == 'active.qualification'*/)) ? true : false;
            }
        else
            return (Auth::check() && $this->status == 'active' && ($this->tender->status == 'active.tendering' /*|| $this->tender->status == 'active.qualification'*/)) ? true : false;
        
        return false;
    }




    public function organizationBids($organizationId)
    {
        return $this->bids()->where('organization_id', $organizationId)->get();
    }

    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function setMinimalStepAttribute($value) {
        $this->attributes['minimal_step'] = round($value * 100);
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function getMinimalStepAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function setGuaranteeAmountAttribute($value) {
        //иногда с прозоро приходил 0 что тоже считается как гарантия отсутствует
        if ($value === NULL || $value == 0) {
            $this->attributes['guarantee_amount'] = NULL;
        } else {
            $this->attributes['guarantee_amount'] = round($value * 100);
        }
    }

    public function getGuaranteeAmountAttribute($value) {

        return isset($value) ? number_format($value / 100, 2, '.', '') : NULL;
    }

    public function qualifications()
    {
        return $this->hasMany('\App\Model\Qualification');
    }

    public function complaint()
    {
        return $this->morphOne('\App\Model\Complaint', 'complaintable');
    }

    public function complaints()
    {
        return $this->morphMany('\App\Model\Complaint', 'complaintable')->where('status', '!=', 'draft');
    }

}
