<?php

namespace App\Services\NotificationService\Model;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    const DEFAULT_LANGUAGE = 'ua';
    const TENDER_SET_DATE = 'tender.set.date';
    const TENDER_CHANGE_DATE = 'tender.change.date';
    const TENDER_UPDATE = 'tender.update';
    const TENDER_PUBLISHED = 'tender.published';
    const TENDER_CANCEL = 'tender.cancel';
    const TENDER_DOCUMENTATION_CHANGE = 'tender.documentation.change';
    const TENDER_STATUS_DATE = 'tender.status.date';
    const TENDER_PRE_QUALIFICATION = 'tender.pre-qualification';
    const OFFER_INPROGRESS = 'offer.inprogress';
    const OFFER_ACCEPT = 'offer.accept';
    const OFFER_UPDATE = 'offer.update';
    const OFFER_DELETE = 'offer.delete';
    const OFFER_CANCEL = 'offer.cancel';
    const OFFER_NEW = 'offer.new';
    const OFFER_PUBLISHED = 'offer.published';
    const OFFER_ERROR_LOWER = 'offer.error.lower';
    const CLAIM_TENDER_NEW = 'claim.tender.new';
    const CLAIM_TENDER_NEW_INFO = 'claim.tender.new.info';
    const CLAIM_TENDER_LOT_NEW = 'claim.tender.lot.new';
    const CLAIM_TENDER_QUALIFICATION_NEW = 'claim.tender.qualification.new';
    const CLAIM_TENDER_WINNER_NEW = 'claim.tender.winner.new';
    const CLAIM_PUBLISHED = 'claim.published';
    const CLAIM_CHANGE = 'claim.change';
    const CLAIM_ANSWER = 'claim.answer';
    const QUESTION_TENDER_NEW = 'question.tender.new';
    const QUESTION_OFFER_NEW = 'question.offer.new';
    const QUESTION_ANSWER = 'question.answer';
    const COMPLAINT_TENDER_NEW = 'complaint.tender.new';
    const COMPLAINT_TENDER_NEW_INFO = 'complaint.tender.new.info';
    const COMPLAINT_OFFER_NEW = 'complaint.offer.new';
    const COMPLAINT_TENDER_QUALIFICATION_NEW = 'complaint.tender.qualification.new';
    const COMPLAINT_QUALIFICATION = 'complaint.qualification';
    const COMPLAINT_PRE_QUALIFICATION = 'complaint.pre-qualification';
    const COMPLAINT_PUBLISHED = 'complaint.published';
    const BALANCE_PLUS = 'balance.plus';
    const BALANCE_MINUS = 'balance.minus';
    const MODE_ACCEPT = 'mode.accept';
    const MODE_CHANGE_TEST = 'mode.change.test';
    const MODE_CHANGE_LIVE = 'mode.change.live';
    const ORGANIZATION_ALLOWED = 'oranization.allowed';
    const ORGANIZATION_NOT_ALLOWED = 'oranization.notallowed';
    const ORGANIZATION_PRE_QUALIFICATION_CANCEL = 'organization.pre-qualification.cancel';
    const ORGANIZATION_CHANGE = 'organization.change';
    const USER_ECP_CREATED = 'user.ecp.created';
    const ORDER_CREATED = 'order.create';
    const ORDER_PLUS = 'order.plus';
    const PLAN_NEW = 'plan.new';
    const PLAN_PUBLISH = 'plan.publish';
    const PLAN_UPDATE = 'plan.update';
    const CONTRACT_UPDATE = 'contract.update';
    const CONTRACT_PUBLISH = 'contract.publish';
    const CONTRACT_ACTIVATE = 'contract.activate';
    const BID_LOSE = 'bid.lose';

    /** @var string */
    protected $table = 'notification_tmp';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alias',
        'title',
        'description',
        'lang',
        'active'
    ];

    /**
     * Scope a query to only active notification templates
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', '=', 1);
    }

    /**
     * @param string $alias
     * @param string $lang
     */
    public function getTemplate($alias, $lang = self::DEFAULT_LANGUAGE)
    {
        return $this->where('alias', '=', $alias)
            ->where('lang', '=', $lang)
            ->active()
            ->first();
    }
}
