<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mode',
        'cbd_id',
        'planID',
        'description',
        'notes',
        'procedure_id',
        'year',
        'code_id',
        'code_additional_id',
        'code_kekv_id',
        'amount',
        'currency_id',
        'start_date',
        'start_day',
        'start_month',
        'start_year',
    ];

    /**
     * The labels for columns.
     *
     * @var array
     */
    private $_labels = [
        'id' => 'ID',
        'planID' => 'Номер',
        'description' => 'Назва',
        'notes' => 'Примітка',
        'year' => 'Рік',
        'amount' => 'Бюджет',
        'start_date' => 'Дата',
        'start_day' => 'День',
        'start_month' => 'Місяць',
        'start_year' => 'Рік',
        'procedure_name' => 'Процедура',
    ];

    /**
     * Return label
     *
     * @param string $field
     *
     * @return string
     */
    public function _getLabel($field)
    {
        return !empty($this->_labels[$field]) ? $this->_labels[$field] : '';
    }




    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function setStartDateAttribute($value) {
        if (!is_null($value)) {
            $this->attributes['start_date'] = Carbon::parse($value)->setTimezone('Europe/Kiev');
        }

        return null;
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function getStartDateAttribute($date) {
        return Carbon::parse($date)->setTimezone('Europe/Kiev')->format('d.m.Y');
    }

    public function currency()
    {
        return $this->hasOne('\App\Model\Currencies', 'id', 'currency_id');
    }

    public function procedure()
    {
        return $this->hasOne('\App\Model\ProcedureTypes', 'id', 'procedure_id');
    }

    public function organization()
    {
        return $this->belongsTo('App\Model\Organization');
    }

    public function items()
    {
        return $this->hasMany('App\Model\PlanItem');
    }

    public function code()
    {
        return $this->hasOne('\App\Model\Codes', 'id', 'code_id');
    }

    public function codeAdditional()
    {
        return $this->hasOne('\App\Model\Codes', 'id', 'code_additional_id');
    }

    public function codeKekv()
    {
        return $this->hasOne('\App\Model\Codes', 'id', 'code_kekv_id');
    }

    public function documents()
    {
        return $this->hasMany('App\Model\PlanDocument');
    }

    public function hasOneClassifier()
    {
        return (strtotime($this->created_at) > strtotime(env('ONE_CLASSIFIER_FROM'))) ? true : false;
    }
}
