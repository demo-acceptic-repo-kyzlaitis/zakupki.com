<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Tender extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'multilot',
        'tenderID',
        'type_id',
        'priority',
        //тип процедури
        'procurement_type_id',
        //тип предмету закупівлі
        'title',
        'title_en',
        'mode',
        'source',
        'auction_url',
        'description',
        'description_en',
        'cause',
        'cause_description',
        'amount',
        'currency_id',
        'tax_included',
        'minimal_step',
        'enquiry_start_date',
        'enquiry_end_date',
        'tender_start_date',
        'tender_end_date',
        'complaint_date_start',
        'complaint_date_end',
        'auction_start_date',
        'auction_end_date',
        'award_start_date',
        'award_end_date',
        'send_to_import',
        'status',
        'cbd_id',
        'date_modified',
        'contact_name',
        'contact_name_en',
        'contact_email',
        'contact_available_lang',
        'contact_phone',
        'contact_url',
        'type_id',
        'blocked',
        'signed',
    ];


    public $type = 'tender';
    public $documentContainerName = '';

    public static $messages = [

    ];

    public static $validation = [
        'multilot'           => '',
        'kind'               => '',
        'tenderID'           => '',
        'title'              => '',
        'mode'               => '',
        'source'             => '',
        'auction_url'        => '',
        'description'        => '',
        'amount'             => '',
        'currency_id'        => '',
        'tax_included'       => '',
        'minimal_step'       => '',
        'enquiry_start_date' => '',
        'enquiry_end_date'   => '',
        'tender_start_date'  => '',
        'tender_end_date'    => '',
        'auction_start_date' => '',
        'auction_end_date'   => '',
        'award_start_date'   => '',
        'award_end_date'     => '',
        'status'             => '',
        'cbd_id'             => '',
        'date_modified'      => '',
    ];

    public function scopeReal($query) {
        return $query->where('mode', 1);
    }

    public function scopeTest($query) {
        return $query->where('mode', 0);
    }

    public function scopeOur($query) {
        return $query->where('source', 1);
    }

    public function scopeWithoutDeleted($query) {
        return $query->where('status', '!=', 'deleted');
    }

    public function getMode() {
        if($this->mode == 0) {
            return 'Тестовий';
        } elseif($this->mode == 1) {
            return 'Реальний';
        }
    }

    public function payments() {
        return $this->morphMany('\App\Model\Transaction', 'payment');
    }

    public function organization() {
        return $this->belongsTo('\App\Model\Organization');
    }

    public function documents() {
        return $this->morphMany('\App\Model\Document', 'documentable');
    }

    public function questions() {
        return $this->morphMany('\App\Model\Question', 'questionable');
    }

    public function allQuestions() {
        return $this->hasMany('\App\Model\Question');
    }


    public function features() {
        return $this->morphMany('\App\Model\Feature', 'featureable');
    }

    public function complaints() {
        return $this->morphMany('\App\Model\Complaint', 'complaintable')->where('status', '!=', 'draft');
    }

    public function allComplaints() {
        return $this->hasMany('\App\Model\Complaint', 'tender_id', 'id');
    }

    public function allItems() {
        return $this->hasManyThrough('App\Model\Item', 'App\Model\Lot');
    }

    public function items() {
        return $this->hasMany('App\Model\Item');
    }

    public function currency() {
        return $this->hasOne('\App\Model\Currencies', 'id', 'currency_id');
    }

    public function statusDesc() {
        return $this->hasOne('\App\Model\Status', 'status', 'status')->where('namespace', 'tender');
    }

    public function cancel() {
        return $this->morphOne('\App\Model\Cancellation', 'cancelable');
    }

    public function tender() {
        return $this->belongsTo('App\Model\Tender', 'id');
    }

    public function bids() {
        return $this->morphMany('\App\Model\Bid', 'bidable');
    }

    public function lot()
    {
        return $this->hasMany('\App\Model\Lot', 'tender_id', 'id');
    }

    public function allBids() {
        return $this->hasMany('\App\Model\Bid');
    }

    public function allQualifications() {
        return $this->hasMany('\App\Model\Qualification')->where('status', '!=', 'cancelled');
    }

    public function awards() {
        return $this->hasMany('\App\Model\Award')->where('status', '!=', 'cancelled');
    }

    public function award() {
        return $this->hasOne('\App\Model\Award')->where('status', '!=', 'cancelled');
    }

    public function awardsAll() {
        return $this->hasMany('\App\Model\Award');
    }

    public function winner() {
        return $this->hasOne('\App\Model\Award')->where('status', '=', 'active');
    }

    public function contracts() {
        return $this->hasMany('\App\Model\Contract')->where('status', '!=', 'cancelled');
    }

    public function errors() {
        return $this->hasMany('\App\Model\TenderErrors');
    }

    public function lots() {
        return $this->hasMany('App\Model\Lot');
    }

    public function myQuestions() {
        return $this->hasMany('\App\Model\Question', 'tender_id');
    }

    public function procurementType() {
        return $this->hasOne('\App\Model\ProcurementType', 'id', 'procurement_type_id');
    }

    public function procedureType() {
        return $this->hasOne('\App\Model\ProcedureTypes', 'id', 'type_id');
    }

    public function procedureTypeDesc() {
        $type_id   = $this->type_id;
        $procedure = ProcedureTypes::where('id', $type_id)->first();

        return $procedure ? $procedure->procedure_name : '';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    public function stages() {
        $field = ($this->procedureType->procurement_method == 'selective') ? 'second_stage' : 'first_stage';

        return $this->hasOne('\App\Model\TenderStages', $field, 'cbd_id');
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'enquiry_start_date',
        'enquiry_end_date',

        'tender_start_date',
        'tender_end_date',

        'auction_start_date',
        'auction_end_date',

        'award_start_date',
        'award_end_date',

        'date_modified',
    ];

    /*Date Setters*/
    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function setMinimalStepAttribute($value) {
        $this->attributes['minimal_step'] = round($value * 100);
    }

    public function setEnquiryStartDateAttribute($value) {
        if(!is_null($value)) {
            $this->attributes['enquiry_start_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }

        return null;
    }

    public function setEnquiryEndDateAttribute($value) {
        if(!is_null($value)) {
            $this->attributes['enquiry_end_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }

        return null;
    }

    public function setTenderStartDateAttribute($value) {
        if(!is_null($value)) {
            $this->attributes['tender_start_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }

        return null;
    }

    public function setTenderEndDateAttribute($value) {
        if(!is_null($value)) {
            $this->attributes['tender_end_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }

        return null;
    }

    public function setAuctionStartDateAttribute($value) {
        $this->attributes['auction_start_date'] = null;
        if(!is_null($value) && !empty($value)) {
            $this->attributes['auction_start_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }
    }

    public function setAuctionEndDateAttribute($value) {
        $this->attributes['auction_end_date'] = null;
        if(!is_null($value) && !empty($value)) {
            $this->attributes['auction_end_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }
    }

    //TODO-PARUS мог поломать
    public function setAwardStartDateAttribute($value) {
        if(!is_null($value)) {
            $this->attributes['award_start_date'] = Carbon::parse($value);
        }
    }

//TODO-PARUS мог поломать
    public function setAwardEndDateAttribute($value) {
        if(!is_null($value)) {
            $this->attributes['award_end_date'] = Carbon::parse($value);
        }
    }

    public function setDateModifiedAttribute($value) {
        $this->attributes['date_modified'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
    }


    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function getMinimalStepAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }


    /*Date Getters*/
    public function getEnquiryStartDateAttribute($date) {
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getEnquiryEndDateAttribute($date) {
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getTenderStartDateAttribute($date) {
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getTenderEndDateAttribute($date) {
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getComplaintDateEndAttribute($date) {
        if($date == null) {

            return null;
        }

        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getAuctionStartDateAttribute($date) {
        if($date == null) {

            return null;
        }

        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getAuctionEndDateAttribute($date) {
        if($date == null) {

            return null;
        }

        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

//TODO-parus мог поломать метод
    public function getAwardStartDateAttribute($date) {
        if($date == null || $date == '0000-00-00 00:00:00') {
            return null;
        }

        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

//TODO-parus мог поломать метод
    public function getAwardEndDateAttribute($date) {
        if($date == null || $date == '0000-00-00 00:00:00') {
            return null;
        }

        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y H:i');
    }

    public function getDateModifiedAttribute($date) {
        if($date == '0000-00-00 00:00:00' || $date == null) {

            return null;
        }

        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('Y-m-d H:i:s');
    }

    public function canSign() {
        return ($this->procedureType->threshold_type == 'above' && $this->status == 'active.awarded') || $this->procedureType->procurement_method_type == 'reporting';
    }

    public function canEdit() {
        $result = false;
        if($this->status == 'draft' || $this->status == 'draft.stage2') {
            $result = true;
        } elseif($this->procedureType->procurement_method_type == 'reporting' && $this->awards()->where('status', 'active')->get()->count() > 0) {
            $result = false;
        } elseif($this->procedureType->procurement_method == 'limited' && $this->status != 'complete' && $this->status != 'cancelled') {
            $result = true;
        } else {
            if(is_object($this->procedureType) && $this->procedureType->procurement_method_type == 'belowThreshold') {
                $result = (strtotime($this->enquiry_end_date) > time() && ($this->status == 'active.enquiries' || $this->status == 'draft')) ? true : false;
            }
            if(is_object($this->procedureType) && (($this->procedureType->procurement_method == 'open' && $this->procedureType->procurement_method_type != 'belowThreshold') || $this->procedureType->procurement_method == 'selective')) {
                if($this->allQuestions()->notAnswered()->count() > 0 || $this->hasNotAnsweredComplaints()) return true;

                $result = (strtotime($this->tender_end_date) > time() && ($this->status == 'active.tendering' || $this->status == 'draft' || $this->status == 'draft.stage2')) ? true : false;
            }

        }

        return $result;
    }

    public function hasNotAnsweredComplaints() {
        if($this->complaints->count() > 0) {
            foreach ($this->complaints as $complaint) {
                if($complaint->status != 'draft' && $complaint->status != 'invalid' && $complaint->status != 'declined' && $complaint->status != 'resolved' && $complaint->status != 'cancelled' && $complaint->status != 'answered') return true;
            }
        }

        foreach ($this->lots as $lot) {
            if($lot->complaints->count() > 0) {
                foreach ($lot->complaints as $complaint) {
                    if($complaint->status != 'draft' && $complaint->status != 'invalid' && $complaint->status != 'declined' && $complaint->status != 'resolved' && $complaint->status != 'cancelled') return true;
                }
            }
        }

        return false;
    }

    public function canPublish() {
        return ($this->published_at == '0000-00-00 00:00:00' && $this->status == 'draft') ? true : false;
    }

    public function isPublished() {
        return $this->access_token != '';
    }

    /**
     * https://zakupki.atlassian.net/browse/ZAK-378
     *
     * @return false если нет комплейнтов в не терминальных статусах тру если есть хоть одни комплейнт
     */
    public function hasPendingComplaints() {
        if($this->complaints->count() > 0) {
            foreach ($this->complaints as $complaint) {
                if($complaint->status == 'pending') {
                    return true;
                }
            }
        }

        foreach ($this->lots as $lot) {
            if($lot->complaints->count() > 0) {
                foreach ($lot->complaints as $complaint) {
                    if($complaint->status == 'pending') {
                        return true;
                    }
                }
            }
        }

        return false;
    }


    /**
     * https://zakupki.atlassian.net/browse/ZAK-378
     *
     * @return bool
     */
    public function canCancelAfterComplaintEndDate() {
        $awardComplaintEndDate = $this->awardsAll()->orderBy('complaint_date_end', 'desc')->lists('complaint_date_end')->first();

        if($awardComplaintEndDate == null) {
            return true;
        }

        $complainDate = Carbon::parse($awardComplaintEndDate);
        $now          = Carbon::now();

        /**
         * Если дата окончания подачи жалобы ПОСЛЕДНЕГО еворда уже прошла
         * то показывать кнопку "Відмінити закупівлю"
         */
        return $now->gte($complainDate) ? true : false;
    }

    public function isStandStill() {
        if($this->status == 'active.pre-qualification.stand-still') {
            return true;
        }

        foreach ($this->lots as $lot) {
            if($lot->status == 'active.pre-qualification.stand-still') {
                return true;
            }
        }

        return false;
    }

    public function canCancel() {
        return ($this->cbd_id != '' && !in_array($this->status, [
                'cancelled',
                'complete',
                'unsuccessful',
            ])) ? true : false;
    }

    public function canTender() {
        return ($this->status == 'active.tendering') ? true : false;
    }

    public function canBid() {
        if (Auth::check() && $this->procedureType && ($this->procedureType->procurement_method == 'open' && $this->procedureType->threshold_type == 'above')) {
            if ($this->status == 'active.qualification')
                return true;
            //return ($this->award && $this->award->bid && $this->award->bid->organization_id == Auth::user()->organization->id) ? true : false;
            elseif ($this->status == 'active.awarded')
                return ($this->winner && $this->winner->bid && $this->winner->bid->organization_id == Auth::user()->organization->id) ? true : false;
            elseif ($this->status == 'active.tendering')
                return true;
        } elseif ($this->procedureType && $this->procedureType->procurement_method == 'selective' && isset($this->stages)) {
            if (($this->status == 'active.tendering' /*|| $this->status == 'active.qualification'*/) && $this->stages->firstStage->allBids()->where('status', 'active')->where('organization_id', Auth::user()->organization->id)->first()) {
                return true;
            }
        } elseif ($this->procedureType && $this->procedureType->procurement_method == 'open' && $this->procedureType->threshold_type == 'below') {
            return (Auth::check() && ($this->status == 'active.tendering' || ($this->status == 'active.qualification' && $this->award && $this->award->bid && $this->award->bid->organization_id == Auth::user()->organization->id))) ? true : false;
        } else {
            return (Auth::check() && ($this->status == 'active.tendering' /*|| $this->status == 'active.qualification'*/)) ? true : false;
        }
        return false;
    }

    public function canQuestion() {
        if($this->procedureType->threshold_type == 'below') {
            return ($this->status == 'active.enquiries' && strtotime($this->enquiry_end_date) > time()) ? true : false;
        } elseif($this->procedureType->procurement_method == 'selective' && isset($this->stages)) {
            if($this->status == 'active.tendering' && strtotime($this->enquiry_end_date) > time() && $this->stages->firstStage->allBids()->where('status', 'active')->where('organization_id', Auth::user()->organization->id)->first()) {
                return true;
            }
        } else {
            return ($this->status == 'active.tendering' && strtotime($this->enquiry_end_date) > time()) ? true : false;
        }
    }

    public function canQualify() {
        return ($this->procedureType->procurement_method == 'limited' && $this->status == 'active') || ($this->status == 'active.qualification' || $this->status == 'active.awarded');
    }

    public function canComplaint() {
        if($this->procedureType->threshold_type == 'below' || $this->procedureType->procurement_method == 'selective') {
            return $this->canBid() || $this->canQuestion();
        } else {
            return time() < strtotime($this->complaint_date_end);
        }
    }


    /**
     * условие для подачи жалобы во время квалификации
     *
     * @return bool
     */
    public function canComplaintQualification() {
        return $this->procedureType->threshold_type == 'above' && $this->status == 'active.qualification';
    }

    public function canContract() {
        return ((($this->status == 'active.awarded' || $this->status == 'active') && ($this->award_end_date == null || (time() - strtotime($this->award_start_date)) > 0 * 3600)) || $this->status == 'complete') ? true : false;
    }

    public function isOwner($userId = null) {
        if(!Auth::check()) {
            return false;
        }
        if($userId == null) {
            $userId = Auth::user()->id;
        }
        $user = $this->organization->user;
        if($user) {

            return ($user->id == $userId) ? true : false;
        }

        return false;
    }

    public function organizationBids($organizationId) {
        return $this->bids()->where('organization_id', $organizationId)->get();
    }

    public function organizationComplaints($organizationId) {
        return $this->complaints()->where('organization_id', $organizationId)->get();
    }

    /**
     * Берет максимульную опцию с каждого нецонового показателя по каждому типу неценового показательня
     * (Тендер, лот, и айтем) и сумирует их всех
     *
     * @return float
     */
    public function getGetMaxFeatureSum() {
        return DB::select('SELECT SUM(val) feature_sum FROM (SELECT max(feature_values.value) AS val FROM features
                                  INNER JOIN feature_values
                                    ON features.id = feature_values.feature_id
                                  WHERE features.tender_id = ?
                                  GROUP BY feature_values.feature_id
                                  HAVING MAX(feature_values.value)) AS max_vals;', [$this->id])[0]->feature_sum;
    }

    public function scopeOpen($query) {
        return $query->whereIn('type_id', [
            1,
            2,
            3,
        ]);
    }

    /**
     * Возвращает название шаблона
     *
     * @return string
     */
    public function getTemplate() {
        switch ($this->type_id) {
            case 4:
                return 'limited';
                break;
            case 1:
                return 'below';
                break;
            default:
                return 'open';
                break;
        }
    }

    public function tenderContacts() {
        return $this->hasMany('\App\Model\TenderContacts', 'tender_id', 'id');
    }

    /**
     * @return bool
     */
    public function hasAnyAcceptedComplaints() {
        $isAnyAcceptedComplaint = collect();


        if($this->isOpen()) {
            foreach ($this->awardsAll as $award) {
                if ($award->complaints()->where('status', 'accepted')->count()) {
                    $isAnyAcceptedComplaint->push($award->complaints()->where('status', 'accepted')->get());
                }
            }
        }
        return $isAnyAcceptedComplaint->count() > 0;
    }

    public function isOpen() {
        //не все открытые процедуры
        $open = [
            'aboveThresholdUA',
            'aboveThresholdEU',
            'aboveThresholdUA.defense',
        ];
        return in_array($this->procedureType->procurement_method_type, $open);
    }

    public function hasOneClassifier()
    {
        return (strtotime($this->created_at) > strtotime(env('ONE_CLASSIFIER_FROM'))) ? true : false;
    }

    public function isForNonResident()
    {
        return in_array($this->type_id, [4,5,6]);
    }

    public function agents() {
        return $this->hasMany('App\Model\Agent');
    }
}

