<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Complaint extends Model
{
    public $documentContainerName = 'complaints';

    protected $fillable =[
        'title',
        'tender_id',
        'organization_id',
        'tender_organization_id',
        'description',
        'type',
        'status',
        'cbd_id',
        'access_token',
        'resolution',
        'resolution_type',
        'cancellation_reason',
        'satisfied',
        'tenderer_action',
        'date_submitted',
        'date_answered',
        'date_escalated',
        'date_decision',
        'date_action'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_submitted',
        'date_answered',
        'date_escalated'
    ];

    public function setDateAnsweredAttribute($value) {
        $this->attributes['date_answered'] = Carbon::parse($value);
    }

    public function setDateSubmittedAttribute($value) {
        $this->attributes['date_submitted'] = Carbon::parse($value);
    }

    public function setDateEscalatedAttribute($value) {
        $this->attributes['date_escalated'] = Carbon::parse($value);
    }

    public function getCreatedAtAttribute($date) {
        return Carbon::parse($date)->format('d.m.y H:i');
    }

    public function getDateSubmittedAttribute($date) {
        return Carbon::parse($date)->format('d.m.y H:i');
    }

    public function getDateAnsweredAttribute($date) {
        return Carbon::parse($date)->format('d.m.y H:i');
    }

    public function getDateEscalatedAttribute($date) {
        return Carbon::parse($date)->format('d.m.y H:i');
    }

    public function setCreatedAtAttribute($value) {
        $this->attributes['created_at'] = Carbon::parse($value);
    }

    public function status(){
        return $this->belongsTo('\App\Model\Status');
    }

    public function documents() {
        return $this->hasMany('\App\Model\ComplaintDocument');
    }

    public function tender()
    {
        if ($this->complaintable->type == 'tender') {
            return $this->complaintable();
        } else {

        }
    }

    public function complaintable()
    {
        return $this->morphTo();
    }

    public function statusDesc() {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'complaint');
    }

    public function organization()
    {
        return $this->belongsTo('\App\Model\Organization');
    }

    static public function activeComplaints($organization)
    {
        if ($organization->type == 'customer') {
            return self::where('tender_organization_id', $organization->id)->where('status', 'claim');
        } else {
            return self::where('organization_id', $organization->id)->where('status', 'answered');
        }

    }

    public function isOwner($userId = null)
    {
        if ($userId == null) {
            $userId = Auth::user()->id;
        }

        if ($this->organization) {
            $user = $this->organization->user;
            if ($user) {

                return ($user->id == $userId) ? true : false;
            }
        }


        return false;
    }

    public function canCancel()
    {
        if ($this->isOwner() && $this->complaintable->tender->status != 'unsuccessful') {
            if ($this->complaintable->type == 'tender' || $this->complaintable->type == 'lot') {
                if ($this->status == 'draft'
                    || (($this->status == 'claim' && $this->type == 'claim') || ($this->status == 'answered' && $this->type == 'claim'))
                    || (($this->status == 'pending' && $this->type == 'complaint'))
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canComplaint()
    {
        if ($this->isOwner() && $this->complaintable->tender->status != 'unsuccessful' && $this->type == 'claim' &&
            ($this->complaintable->tender->procedureType->threshold_type == 'above' ||
                $this->complaintable->tender->procedureType->threshold_type == 'below')) {
            if ($this->complaintable->type == 'tender' || $this->complaintable->type == 'lot') {
                if ($this->status == 'draft'
                    || (($this->status == 'claim' && $this->type == 'claim') || ($this->status == 'answered' && $this->type == 'claim'))
                ) {
                    return true;
                }
            }
            if ($this->complaintable->type == 'qualification') {
                if ($this->status == 'draft') {
                    return true;
                }
            }
            if ($this->complaintable->type == 'award') {
                if ($this->status == 'draft') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Достает все скарги
     *
     * @param $query
     * @return mixed
     */
    public function scopeUnsatisfiedComplaints($query)
    {
        return $query->where('type', 'complaint')->where('satisfied', 0)->where('status', 'pending');
    }

}

