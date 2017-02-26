<?php

namespace App\Model;

use App\Services\NotificationService\Model\NotificationTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'alias',
        'notificationable_id',
        'notificationable_type',
        'readed_at',
        'sended_at',
        'title',
        'text',
        'status'
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i');
    }

    /**
     * Scope a query to only new notification
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotReaded($query)
    {
        return $query->where('readed_at', '=', null)->where('status', '!=', 'log');
    }

    /**
     * Scope a query to only user notification
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePersonal($query)
    {
        //Сомнительная связанность модели и авторизированого пользователя
        return $query->where('user_id', Auth::user()->id);
    }

    public function getEvent(){
        $alias = $this->alias;
        $new_entities = [
            NotificationTemplate::TENDER_PUBLISHED => 'Закупівлю опубліковано',
            NotificationTemplate::TENDER_SET_DATE => 'Призначено дату закупівлі',
            NotificationTemplate::TENDER_CHANGE_DATE => 'Змінено дату закупівлі',
            NotificationTemplate::TENDER_STATUS_DATE => 'Підтверджено дату закупівлі',
            NotificationTemplate::TENDER_CANCEL => 'Закупівлю скасовано',
            NotificationTemplate::TENDER_UPDATE => 'Закупівлю змінено',
            NotificationTemplate::TENDER_PRE_QUALIFICATION => 'Оскарження результатів прекваліфікації по закупівлі',
            NotificationTemplate::TENDER_DOCUMENTATION_CHANGE => 'Тендерна документація',
            NotificationTemplate::CLAIM_TENDER_NEW => 'Нова вимога',
            NotificationTemplate::CLAIM_TENDER_LOT_NEW => 'Нова вимога',
            NotificationTemplate::CLAIM_TENDER_QUALIFICATION_NEW => 'Нова вимога',
            NotificationTemplate::CLAIM_TENDER_WINNER_NEW => 'Нова вимога',
            NotificationTemplate::CLAIM_ANSWER => 'Отримано відповідь',
            NotificationTemplate::CLAIM_PUBLISHED => 'Вимогу опубліковано',
            NotificationTemplate::CLAIM_CHANGE => 'Вимогу змінено',
            NotificationTemplate::COMPLAINT_TENDER_NEW => 'Нова скарга',
            NotificationTemplate::COMPLAINT_OFFER_NEW => 'Нова скарга',
            NotificationTemplate::COMPLAINT_TENDER_QUALIFICATION_NEW => 'Нова скарга',
            NotificationTemplate::COMPLAINT_PUBLISHED => 'Скаргу опубліковано',
            NotificationTemplate::COMPLAINT_QUALIFICATION => 'Скарга на результат кваліфікації',
            NotificationTemplate::COMPLAINT_PRE_QUALIFICATION => 'Скарга на результат прекваліфікації',
            NotificationTemplate::QUESTION_TENDER_NEW => 'Нове запитання',
            NotificationTemplate::QUESTION_ANSWER => 'Надано',
            NotificationTemplate::OFFER_ERROR_LOWER => 'Помилка в пропозиції',
            NotificationTemplate::OFFER_DELETE => 'пропозицію видалено',
            NotificationTemplate::OFFER_ACCEPT => 'Акцептовано',
            NotificationTemplate::OFFER_INPROGRESS => 'Пропозиція розглядається',
            NotificationTemplate::OFFER_UPDATE => 'Змінено',
            NotificationTemplate::OFFER_CANCEL => 'Пропозицію відмінено',
            NotificationTemplate::OFFER_NEW => 'Пропозиція',
            NotificationTemplate::OFFER_PUBLISHED => 'Пропозиція',
            NotificationTemplate::USER_ECP_CREATED => 'Інформування',
            NotificationTemplate::PLAN_PUBLISH => 'Опубліковано',
            NotificationTemplate::PLAN_NEW =>'Створено',
            NotificationTemplate::PLAN_UPDATE => 'Оновлено',
            NotificationTemplate::BALANCE_PLUS => 'Рахунок поповнено',
            NotificationTemplate::BALANCE_MINUS => 'Відраховано',
            NotificationTemplate::MODE_ACCEPT => 'Режим роботи',
            NotificationTemplate::MODE_CHANGE_TEST=> 'Режим роботи',
            NotificationTemplate::MODE_CHANGE_LIVE => 'Режим роботи',
            NotificationTemplate::ORGANIZATION_CHANGE => 'Змінено',
            NotificationTemplate::ORGANIZATION_ALLOWED => 'Допуск організації до участі в аукціоні',
            NotificationTemplate::ORGANIZATION_NOT_ALLOWED => 'Організацію не допущено до участі в аукціоні',
            NotificationTemplate::ORGANIZATION_PRE_QUALIFICATION_CANCEL => 'Прекваліфікації організації відмінена',
            NotificationTemplate::ORDER_CREATED =>'Створено',
            NotificationTemplate::CONTRACT_UPDATE =>'Змінено',
            NotificationTemplate::CONTRACT_PUBLISH =>'Cтворено нову пропозицію.',
            NotificationTemplate::OFFER_NEW => 'Створено',
            NotificationTemplate::OFFER_ACCEPT => 'Акцептовано',
            NotificationTemplate::OFFER_CANCEL => 'Відхилено',
            NotificationTemplate::BID_LOSE => 'Відхилено'
        ];
        $old_entities = [
            'tender.auction.start'=>'Призначено дату',
            'tender.auction.status'=>'Призначено дату',
            'tender.auction.change'=>'Змінено дату',
            'tender.new' => 'Створено',
            'tender.cancel.general'=>'Скасовано',
            'tender.cancel.question' => 'Опубліковано',
            'tender.cancel.bid'=>'Відмінено',
            'award.considered'=>'Пропозиція',
            'complaint.new'=>'Створено',
            'claim.new'=>'Створено',
            'claim.get'=>'Отримано',
            'claim.award.new'=>'Створено',
            'claim.answer'=>'Отримано відповідь',
            'claim.published'=>'Опубліковано',
            'complaint.published'=>'Опубліковано',
            'complaint.get'=>'Отримано',
            'complaint.change'=>'Перетворено',
            'claim.сomplaint'=>'Вимога',
            'question.answer'=>'Надано',
            'claim.qualification.new'=>'Надано',
            'question.new'=>'Створено',
            'question.get'=>'Отримано',
            'award.cancelled'=>'Пропозиція',
            'plan.update' => 'Оновлено',
            'plan.sign' => 'Підписано',
            'bid.new'=>'Створено',
            'bid.update'=>'Змінено',
            'bid.delete'=>'Видалено',
            'ecp.created'=>'Інформування',
            'bid.published'=>'Опубліковано',
            'bid.updating'=>'Змінено',
            'plan.published'=>'Опубліковано',
            'plan.new'=>'Створено',
            'balance.plus'=>'Поповнено',
            'balance.minus'=>'Відраховано',
            'organization.change'=>'Змінено',
            'mode.accept'=>'Доступний',
            'mode.change'=>'Змінено',
            'order.create'=>'Створено',
            'contracts.new'=>'Створено',
            'contracts.update'=>'Змінено',
            'contracts.published'=>'Опубліковано',
            'contract.activate'=>'Підтверджено підписання',
            'award.new'=>'Створено',
            'award.accept'=>'Акцептовано',
            'award.canceled'=>'Відхилено',
            'information'=>'Інформація',
            'tender.pre-qualification.bid'=>'Розпочато',
            'qualification.active'=>'Допущено',
            'custom.notification' =>'Інформація'
        ];
        $entities = array_merge($new_entities, $old_entities);
        if(isset($entities[$alias])){
            return $entities[$alias];
        } else {
            return $alias;
        }
    }
}
