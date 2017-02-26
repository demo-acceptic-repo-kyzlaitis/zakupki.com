<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Bid extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status',
        'participation_url',
        'organization_id',
        'cbd_id',
        'amount',
        'signed',
        'tax_included',
        'currency_id',
        'tender_id',
        'payment_amount',
        'self_qualified',
        'self_eligible',
        'subcontracting_details',
        'qualify_qualified',
        'qualify_eligible',
        'qualify_unsuccessful_title',
        'qualify_unsuccessful_description'
    ];

    protected $dates = ['deleted_at'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('amount', 'asc');
    }

    public function scopeOnlyOur($query)
    {
        return $query->where('access_token', '!=', '');
    }

    public function status()
    {
        return $this->belongsTo('\App\Model\Status');
    }

    public function documents()
    {
        return $this->hasMany('\App\Model\BidDocuments');
    }

    public function bidable()
    {
        return $this->morphTo();
    }

    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender');
    }

    public function lot()
    {
        return $this->hasOne('\App\Model\Lot', 'tender_id', 'tender_id');
    }

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    public function award()
    {
        return $this->hasOne('\App\Model\Award')->where('status', '!=', 'cancelled');
    }

    public function qualification()
    {
        return $this->hasOne('\App\Model\Qualification')->where(function ($query) {
            $query->orWhere('status', 'active')->orWhere('status', 'pending')->orWhere('status', 'unsuccessful');
        });
    }

    public function currency() {
        return $this->hasOne('\App\Model\Currencies', 'id', 'currency_id');
    }

    public function values()
    {
        return $this->belongsToMany('\App\Model\FeatureValue');
    }

    public function isOwner($userId = null)
    {
        if ($userId == null) {
            $userId = Auth::user()->id;
        }

        if ($this->organization && $this->organization->user) {
            return $this->organization->user->id == $userId;
        }

        return false;
    }

    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }
    public function getStatus(){
        if($this->bidable_type == 'App\Model\Lot'){
            return 'Лот';
        }elseif($this->bidable_type == 'App\Model\Tender'){
            return 'Тендер';
        }else{
            return $this->bidable_type;
        }
    }

    public function statusDesc()
    {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'bid');
    }

    public function amountHistory() {
        return $this->morphMany('\App\Model\History', 'historyable')->where('alias', 'bid.amount')->orderBy('created_at', 'desc');
    }

    public function featureValueHistory() {
        return $this->morphMany('\App\Model\History', 'historyable')->where('alias', 'feature.value')->orderBy('created_at', 'desc');
    }

    public function canDownloadDocsFromUs() {
        return (Auth::check() && Auth::user()->organization->id == $this->organization_id && ($this->tender->status == 'active.tendering' || $this->tender->status == 'active.auction')) ? true : false;
    }
}
