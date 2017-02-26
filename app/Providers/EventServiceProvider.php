<?php

namespace App\Providers;

use App\Events\ReturnMoneyEvent;
use App\Events\ReturnMoneyForInvalidBidEvent;
use App\Model\Award;
use App\Model\Bid;
use App\Model\Cancellation;
use App\Model\Complaint;
use App\Model\Contract;
use App\Model\Feature;
use App\Model\GroundsForRejection;
use App\Model\Item;
use App\Model\Lot;
use App\Model\Order;
use App\Model\Organization;
use App\Model\Plan;
use App\Model\PlanningItem;
use App\Model\Qualification;
use App\Model\Question;
use App\Model\Tender;
use App\Model\UserBalance;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

class EventServiceProvider extends ServiceProvider
{
    const DEFAULT_LANGUAGE = 'ua';

    /** @var NotificationService */
    protected $_notification_service;

    /** @var Tags */
    private $_tags;

    /** @var string */
    private $_lang;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\UserRegisterEvent' => [
            'App\Listeners\UserRegisterListener',
        ],
        'App\Events\TenderSaveEvent' => [
            'App\Listeners\TenderSaveListener',
            //'App\Listeners\TenderSaveListenerSync',
        ],
        'App\Events\TenderPublishEvent' => [
            //'App\Listeners\TenderSaveListener',
            'App\Listeners\TenderPublishEmailListener',
        ],
        'App\Events\TenderUpdateEvent' => [
            'App\Listeners\TenderUpdateListener',
        ],
        'App\Events\QuestionCreateEvent' => [
            'App\Listeners\QuestionCreateListener',
        ],
        'App\Events\DocumentUploadEvent' => [
            'App\Listeners\DocumentUploadListener'
        ],
        'App\Events\PlanDocumentUploadEvent' => [
            'App\Listeners\PlanDocumentUploadListener'
        ],
        'App\Events\TenderAnswerEvent' => [
            'App\Listeners\TenderAnswerListener'
        ],
        'App\Events\CancelSaveEvent' => [
            'App\Listeners\CancelSaveListener',
        ],
        'App\Events\CancelDocUploadEvent' => [
            'App\Listeners\CancelDocUploadSaveListener',
        ],
        'App\Events\CancelActivateEvent' => [
            'App\Listeners\CancelActivateListener',
        ],
        'App\Events\BidSaveEvent' => [
            'App\Listeners\BidSaveListener',
        ],
        'App\Events\BidDocUploadEvent' => [
            'App\Listeners\BidDocUploadSaveListener',
        ],
        'App\Events\BidDeleteEvent' => [
            'App\Listeners\BidDeleteListener',
        ],
        'App\Events\AwardSaveEvent' => [
            'App\Listeners\AwardSaveListener',
        ],
        'App\Events\AwardDocUploadEvent' => [
            'App\Listeners\AwardDocUploadSaveListener',
        ],
        'App\Events\ComplaintSaveEvent' => [
            'App\Listeners\ComplaintSaveEventListener',
        ],
        'App\Events\QualificationSaveEvent' => [
            'App\Listeners\QualificationSaveListener',
        ],
        'App\Events\QualificationDocUploadEvent' => [
            'App\Listeners\QualificationDocUploadSaveListener',
        ],
        'App\Events\TenderChangeAuctionDateEvent' => [
            'App\Listeners\TenderChangeAuctionDateListener',
        ],
        'App\Events\ComplaintDocUploadEvent' => [
            'App\Listeners\ComplaintUploadSaveListener',
        ],
        'App\Events\PlanSaveEvent' => [
            'App\Listeners\PlanSaveListener',
        ],
        'App\Events\ContractSaveEvent' => [
            'App\Listeners\ContractSaveListener',
        ],
        'App\Events\ContractDocUploadEvent' => [
            'App\Listeners\ContractDocUploadSaveListener',
        ],
        'App\Events\ComplaintCancelEvent' => [
            'App\Listeners\ComplaintCancelListener'
        ],
        'App\Events\WithdrowalEvent' => [
            'App\Listeners\WithdrowalListener'
        ],
        'App\Events\ReturnMoneyEvent' => [
            'App\Listeners\ReturnMoneyListener'
        ],
        'App\Events\RefillBalanceEvent' => [
            'App\Listeners\RefillBalanceListener'
        ],
        'App\Events\TenderUpdatesEvent' => [
            'App\Listeners\TenderUpdatesListener'
        ],
        'App\Events\BidAcceptEvent' => [
            'App\Listeners\BidAcceptListener'
        ],
        'App\Events\BidUpdateEvent' => [
            'App\Listeners\BidUpdateListener'
        ],
        'App\Events\TargetNotificationEvent' => [
            'App\Listeners\TargetNotificationListener'
        ],
        'App\Events\PatchChangeDocumentEvent' => [
            'App\Listeners\PatchChangeDocumentListener'
        ],
        'App\Events\ReturnMoneyForInvalidBidEvent' => [
            'App\Listeners\ReturnMoneyForInvalidBidListener'
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
        $this->_notification_service = new NotificationService();
        $this->_tags = new Tags();
        $this->_lang = self::DEFAULT_LANGUAGE;

        Item::creating(function ($item) {
            if (!isset($item->cbd_id) || $item->cbd_id === "") {
                $item->cbd_id = sha1(uniqid(mt_rand(), true));
            }
        });

        Feature::creating(function ($feature) {
            if (empty($feature->cbd_id)) {
                $feature->cbd_id = sha1(uniqid(mt_rand(), true));
            }
        });

        Lot::creating(function ($lot) {
            if (empty($lot->cbd_id) && empty($lot->tender->cbd_id)) {
                $lot->cbd_id = sha1(uniqid(mt_rand(), true));
            }
        });

        PlanningItem::creating(function ($item) {
            if (empty($item->cbd_id)) {
                $item->cbd_id = sha1(uniqid(mt_rand(), true));
            }
        });

        Tender::updating(function ($tender) {

            if (!empty($tender->tenderID) && $tender->getOriginal('tenderID') == '') {
                $this->_sendNotification($this->_tags, $tender, NotificationTemplate::TENDER_PUBLISHED);
            }
            if ($tender->status == 'cancelled' && $tender->getOriginal('status') != 'cancelled') { //если тенедр был отменен
                Event::fire(new ReturnMoneyEvent($tender));
                $questions = Question::where('tender_id', $tender->id)->get();
                $organizations = [];
                foreach ($questions as $question) {
                    if ($question->organization && $question->organization->user) {
                        $organizations[$question->organization->id] = $question->organiztion;
                    }
                }
                foreach ($tender->complaints as $complaint) {
                    if ($complaint->organization && $complaint->organization->user) {
                        $organizations[$complaint->organization->id] = $complaint->organization;
                    }
                }
                foreach ($tender->allBids as $bid) {
                    if ($bid->access_token != '' && $bid->organization && $bid->organization->user) {
                        $organizations[$bid->organization->id] = $bid->organization;
                    }
                }

                foreach($organizations as $organization)
                    if ($organization->user)
                        $this->_sendNotification($this->_tags, $organization, NotificationTemplate::TENDER_CANCEL);
            }
            if ($tender->status == 'active.auction' && $tender->getOriginal('status') != 'active.auction') {
                foreach($tender->allBids as $model) {
                    $this->_sendNotification($this->_tags, $model, NotificationTemplate::TENDER_STATUS_DATE);
                }
            }
            if ($tender->status == 'active.pre-qualification.stand-still' && $tender->getOriginal('status') != 'active.pre-qualification.stand-still') {
                foreach ($tender->allBids as $model) {
                    $this->_sendNotification($this->_tags, $model, NotificationTemplate::TENDER_PRE_QUALIFICATION);
                }
            }
        });

        Question::creating(function ($question) {
            if (!empty($question->tender_id)) {
                $tender = Tender::find($question->tender_id);
                $this->_sendNotification($this->_tags, $tender, NotificationTemplate::QUESTION_TENDER_NEW);
                foreach ($tender->allBids as $model) {
                    if ($question->organization && $model->organization && $question->organization->id != $model->organization->id) {
                        $this->_sendNotification($this->_tags, $model, NotificationTemplate::QUESTION_OFFER_NEW);
                    }
                }
            }
        });

        Question::updating(function ($question) {
            if ($question->answer != $question->getOriginal('answer')) {
                $this->_sendNotification($this->_tags, $question, NotificationTemplate::QUESTION_ANSWER);
            }
        });

        Complaint::creating(function ($complaint) {
            if ($complaint->complaintable_type == 'App\Model\Tender' && $complaint->status !== 'draft') {
                $tender = Tender::find($complaint->complaintable_id);
                $this->_sendNotification($this->_tags, $tender, NotificationTemplate::CLAIM_TENDER_NEW);
            }
            if ($complaint->complaintable_type == 'App\Model\Lot' && $complaint->status !== 'draft') {
                $lot = Lot::find($complaint->complaintable_id);
                $this->_sendNotification($this->_tags, $lot->tender, NotificationTemplate::CLAIM_TENDER_NEW);
            }
            if ($complaint->complaintable_type == 'App\Model\Award' && $complaint->status !== 'draft') {
                $award = Award::find($complaint->complaintable_id);
                $this->_sendNotification($this->_tags, $award->tender, NotificationTemplate::COMPLAINT_QUALIFICATION);
            }
            if ($complaint->complaintable_type == 'App\Model\Qualification' && $complaint->status !== 'draft') {
                $qualification = Qualification::find($complaint->complaintable_id);
                $this->_sendNotification($this->_tags, $qualification->bid->tender, NotificationTemplate::COMPLAINT_PRE_QUALIFICATION);
            }
        });

        /*Complaint::updating(function ($complaint) {
            $tender = $complaint->complaintable->tender;
            foreach ($tender->allBids as $bid) {
                if (($complaint->status != $complaint->getOriginal('status') && $complaint->getOriginal('status') == 'draft')
                    && $complaint->organization->id != $bid->organization->id
                ) {
                    if ($complaint->type == 'claim')
                        $this->_sendNotification($this->_tags, $bid, NotificationTemplate::CLAIM_TENDER_NEW_INFO);
                    if ($complaint->type == 'complaint')
                        $this->_sendNotification($this->_tags, $bid, NotificationTemplate::COMPLAINT_TENDER_NEW_INFO);
                }
            }
            if ($complaint->type == 'claim' && $complaint->getOriginal('status') =='draft' && $complaint->status !='draft')  {
                $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::CLAIM_PUBLISHED);
            }
            if ($complaint->type == 'complaint' && $complaint->getOriginal('type') == 'claim' && $complaint->getOriginal('status') == 'draft')  {
                $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::COMPLAINT_PUBLISHED);
            }

            if ($complaint->status == 'claim' && $complaint->getOriginal('status') == 'draft') {
                if ($complaint->complaintable_type == 'App\Model\Tender') {
                    $tender = Tender::find($complaint->complaintable_id);
                    $this->_sendNotification($this->_tags, $tender, NotificationTemplate::CLAIM_TENDER_NEW);
                }
                if ($complaint->complaintable_type == 'App\Model\Lot') {
                    $lot = Lot::find($complaint->complaintable_id);
                    $this->_sendNotification($this->_tags, $lot->tender, NotificationTemplate::CLAIM_TENDER_LOT_NEW);
                }
                if ($complaint->complaintable_type == 'App\Model\Qualification') {
                    $qualification = Qualification::find($complaint->complaintable_id);
                    $this->_sendNotification($this->_tags, $qualification->bid->tender, NotificationTemplate::CLAIM_TENDER_QUALIFICATION_NEW);
                }
                if ($complaint->complaintable_type == 'App\Model\Award') {
                    $award = Award::find($complaint->complaintable_id);
                    $this->_sendNotification($this->_tags, $award->tender, NotificationTemplate::CLAIM_TENDER_WINNER_NEW);
                }
            }

            if($complaint->status == 'answered' && $complaint->getOriginal('status') != 'answered') {
                $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::CLAIM_ANSWER);

//                if ($complaint->complaintable_type == 'App\Model\Tender') {
//                    $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::CLAIM_ANSWER);
//                }
//                if ($complaint->complaintable_type == 'App\Model\Lot') {
//                    $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::CLAIM_ANSWER);
//                }
//                if ($complaint->complaintable_type == 'App\Model\Qualification') {
//                    $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::CLAIM_ANSWER);
//                }
//                if ($complaint->complaintable_type == 'App\Model\Award') {
//                    $this->_sendNotification($this->_tags, $complaint, NotificationTemplate::CLAIM_ANSWER);
//                }
            }
            if ($complaint->type == 'complaint' && $complaint->getOriginal('type') != 'complaint') {
                if ($complaint->complaintable->type == 'tender') {
                    $this->_sendNotification($this->_tags, Tender::find($complaint->complaintable_id), NotificationTemplate::COMPLAINT_TENDER_NEW);
                } elseif ($complaint->complaintable->type == 'lot') {
                    $lot = Lot::find($complaint->complaintable_id);
                    $this->_sendNotification($this->_tags, $lot->tender, NotificationTemplate::COMPLAINT_TENDER_NEW);
                } elseif ($complaint->complaintable->type == 'qualification') {
                    $qualification = Qualification::find($complaint->complaintable_id);
                    $this->_sendNotification($this->_tags, $qualification->bid->tender, NotificationTemplate::COMPLAINT_TENDER_QUALIFICATION_NEW);
                } else {
                    $this->_sendNotification($this->_tags, $complaint->complaintable, NotificationTemplate::CLAIM_CHANGE);
                }
            }
        });*/

        Award::creating(function ($award) {
            if (!empty($award->bid_id)) {
                $bid = Bid::find($award->bid->id);
                $this->_sendNotification($this->_tags, $bid, NotificationTemplate::OFFER_INPROGRESS);
            }
        });

        Award::updating(function ($award) {
            if ($award->bid &&
                (($award->status == 'unsuccessful' &&
                        $award->getOriginal('status') != 'unsuccessful') ||
                    ($award->status == 'active' &&
                        $award->getOriginal('status') == 'activate'))
            ) {
                $bid = Bid::find($award->bid->id);
                if ($bid->organization && $bid->organization->user) {
                    $this->_sendNotification($this->_tags, $bid, $award->status == 'unsuccessful' ?
                        NotificationTemplate::OFFER_CANCEL :
                        NotificationTemplate::OFFER_ACCEPT);
                }
            }

            if ($award->bid && $award->status == 'active' && $award->getOriginal('status') == 'activate') {
                $tender = Tender::find($award->tender_id);
                $bid = Bid::find($award->bid->id);

                $complaintEndText = ($award->complaint_date_end) ? ' до ' . $award->complaint_date_end : '.';
                $bids = $bid->bidable->bids;
                foreach ($bids as $item) {
                    if ($bid->id != $item->id) {
                        $this->_sendNotification($this->_tags, $item, NotificationTemplate::BID_LOSE);
                    }
                }
            }
        });

        Organization::updating(function ($organization) {
            if ($organization->confirmed == 1 && $organization->getOriginal('confirmed') == 0) {
                $this->_sendNotification($this->_tags, $organization, NotificationTemplate::MODE_ACCEPT);
            } elseif ($organization->mode == 0  && $organization->getOriginal('mode') == 1){
                $this->_sendNotification($this->_tags, $organization, NotificationTemplate::MODE_CHANGE_TEST);
            } elseif ($organization->mode == 1  && $organization->getOriginal('mode') == 0){
                $this->_sendNotification($this->_tags, $organization, NotificationTemplate::MODE_CHANGE_LIVE);
            } else {
                $this->_sendNotification($this->_tags, $organization, NotificationTemplate::ORGANIZATION_CHANGE);
            }
        });

        Order::creating(function ($order){
            $this->_sendNotification($this->_tags, $order, NotificationTemplate::ORDER_CREATED);
        });

        UserBalance::creating(function ($balance){
            $this->_sendNotification($this->_tags, $balance, NotificationTemplate::ORDER_PLUS);
        });

        UserBalance::updating(function ($balance){
            if ($balance->amount > $balance->getOriginal('amount') / 100){
                $this->_sendNotification($this->_tags, $balance, NotificationTemplate::BALANCE_PLUS);
            } else {
                $this->_sendNotification($this->_tags, $balance, NotificationTemplate::BALANCE_MINUS);
            }
        });

        Plan::creating(function ($plan) {
            $this->_sendNotification($this->_tags, $plan, NotificationTemplate::PLAN_NEW);
        });

        Plan::updating(function ($plan) {
            $this->_sendNotification($this->_tags, $plan, !empty($plan->cbd_id) && $plan->getOriginal('cbd_id') == '' ?
                NotificationTemplate::PLAN_PUBLISH :
                NotificationTemplate::PLAN_UPDATE
            );
        });

        Contract::updating(function ($contract) {
            $this->_sendNotification($this->_tags, $contract, !empty($contract->cbd_id) && $contract->getOriginal('cbd_id') == '' ?
                NotificationTemplate::CONTRACT_PUBLISH :
                NotificationTemplate::CONTRACT_UPDATE
            );
        });

        Bid::creating(function ($bid) {
            $this->_sendNotification($this->_tags, $bid,  NotificationTemplate::OFFER_NEW);
        });

        Bid::updating(function ($bid) {
            if (!empty($bid->cbd_id) && $bid->getOriginal('cbd_id') == '') {
                $this->_tags->set_text($bid->tender->procedureType->threshold_type == 'above' ? ' та накласти ецп' : '');
                $this->_sendNotification($this->_tags, $bid,  NotificationTemplate::OFFER_PUBLISHED);
            }

            if ($bid->status == 'invalid' && $bid->getOriginal('status') != 'invalid') {
                $this->_sendNotification($this->_tags, $bid,  NotificationTemplate::TENDER_DOCUMENTATION_CHANGE);
            }
        });
        Qualification::updating(function ($qualification) {
            $bid = $qualification->bid;
            if ($qualification->status == 'active' && $qualification->getOriginal('status') != 'active' && $bid->organization->user) {
                $this->_sendNotification($this->_tags, $bid,  NotificationTemplate::ORGANIZATION_ALLOWED);
            }
            if ($qualification->status == 'unsuccessful' && $qualification->getOriginal('status') != 'unsuccessful') {
                $strUnsuccessfulCauses = '';
                $arUnsuccessfulCause = json_decode($qualification->title);
                if (!empty($arUnsuccessfulCause)) {
                    $groundsForRejections = GroundsForRejection::prequalification()->lists('title', 'id');
                    foreach ($arUnsuccessfulCause as $cause) {
                        $strUnsuccessfulCauses .= $groundsForRejections[$cause] . ', ';
                    }
                    $strUnsuccessfulCauses = substr($strUnsuccessfulCauses, 0, -2);
                }
                $this->_tags->set_text($strUnsuccessfulCauses);
                $this->_sendNotification($this->_tags, $bid,  NotificationTemplate::ORGANIZATION_NOT_ALLOWED);
            }
            if ($qualification->status == 'cancelled' && $qualification->getOriginal('status') != 'cancelled') {
                $this->_sendNotification($this->_tags, $bid,  NotificationTemplate::ORGANIZATION_PRE_QUALIFICATION_CANCEL);
            }
        });

        Cancellation::updating(function ($cancellation) {
            if(($cancellation->status == 'active' && $cancellation->getOriginal('status') == 'pending') &&
                ($cancellation->tender->status == 'active.tendering' || $cancellation->tender->status == 'active.enquiries')) {

                //Только для закупок в реальном режиме
                if($cancellation->tender->mode == 1) {
                    //TODO almost the same piece of code
                    if($cancellation->cancelable->type == 'lot') {
                        $ourBidsOfCancelledLot = $cancellation->cancelable->bids()->whereHas('organization', function($q) {
                            $q->where('source', 0);
                        })->get();

                        foreach ($ourBidsOfCancelledLot as $bid) {
                            Event::fire(new ReturnMoneyForInvalidBidEvent($bid));
                        }
                    }

                    //TODO almost the same piece of code
                    if($cancellation->cancelable->type == 'tender') {
                        $ourBidsOfCancelledTender = $cancellation->cancelable->tender->allBids()->whereHas('organization', function($q) {
                            $q->where('source', 0);
                        })->get();

                        foreach ($ourBidsOfCancelledTender as $bid) {
                            Event::fire(new ReturnMoneyForInvalidBidEvent($bid));
                        }
                    }
                }
            }
        });

    }

    /**
     * @param Tags $tags
     * @param Model $model
     * @param string $alias
     */
    protected function _sendNotification(Tags $tags, Model $model, $alias)
    {
        if ((!empty($model->organization) && !empty($model->organization->user)) ||
            !empty($model->user) ||  !empty($model->user_id)
        ) {
            $user_id = !empty($model->organization->user->id) ?
                $model->organization->user->id :
                (!empty($model->user->id) ?
                    $model->user->id :
                    (!empty($model->user_id) ? $model->user_id : 0));

            $tender = ($model instanceof Tender) ?
                $model :
                (!empty($model->tender) ?
                    $model->tender :
                    (!empty($model->tender_id) && Tender::find($model->tender_id) ?
                        Tender::find($model->tender_id) :
                        null));

            $tender_id = !empty($tender->id) ? $tender->id : null;
            $tender_name = !empty($tender->tenderID)? $tender->tenderID : null;

            if(!empty($model->bidable->auction_start_date)){
                $tags->set_tender_date($model->bidable->auction_start_date);
            }
            if(!empty($tender_name)){
                $tags->set_tender_name($tender_name);
            }
            if(!empty($tender_name) && !empty($tender_id)){
                $tags->set_tender_link("<a href='" . URL::route('tender.show', [$tender_id])."'>".$tender_name."</a>");
                $tags->set_claim_link("<a href='" . URL::route('claim.index', ['tender', $tender_id])."''>".$tender_name."</a>");
            }
            if(!empty($model->amount)){
                $sum = abs($model->getOriginal('amount') / 100 - $model->amount);
                $tags->set_balance_sum($sum);
            }

            $tags->set_offers_link("<a href='" . URL::route('bid.list') . "'>Мої пропозиції</a>");
            $tags->set_balance_link("<a href='" . URL::route('Payment.pay') . "'>Баланс</a>.");
            $tags->set_plans_link("<a href='" . URL::route('plan.list') . "'>Мої плани закупівель</a>");
            $tags->set_organization_link("a href='" . URL::route('organization.edit') . "'>Редагування організації</a>");
            if (!empty($model->organization)) {
                $tags->set_organization_name(!empty($model->organization->name) ? $model->organization->nam : "");
                $tags->set_organization_address(!empty($model->organization->getAddress()) ? $model->organization->getAddress() : '');
            }

            $this->_notification_service = !empty($this->_notification_service) ? $this->_notification_service : new NotificationService();
            $this->_notification_service->create(
                $tags,
                $alias,
                $user_id,
                $this->_lang
            );
        }
    }
}
