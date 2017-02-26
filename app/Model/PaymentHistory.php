<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class PaymentHistory extends Model
{
    const UAH = 'UAH';
    const PENDING = 'pending';
    const LIQPAY = 1;
    const CASHLESS = 2;
    const BILLING = 3;
    const COMPLETED = 'completed';
    protected $table = 'payment_history';

    public function payment_service()
    {
        return $this->hasOne('App\Model\PaymentService', 'id', 'payment_services');
    }
    public function scopeLiqPay($query)
    {
        return $query->where('payment_services', 1);
    }

    public function scopeCashless($query)
    {
        return $query->where('payment_services', 2);
    }
    public function scopeInvoice($query)
    {
        return $query->where('payment_services', 3);
    }
    public function scopePending($query)
    {
        return $query->where('status_ps', 'pending');
    }
    public function scopeCompleted($query)
    {
        return $query->where('status_ps', 'completed');
    }

    public function order()
    {
        return $this->hasOne('App\Model\Order', 'id', 'payment_history_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
    }

    public function getOurStatus(){
        $status = $this->status_our;
        if ($status == 0){
            echo '<p style="color: sandybrown">Pending</p>';
        }elseif($status == 1){
            echo '<p style="color: green">Completed</p>';
        }
    }

    public static function getPaymentAmount($amount)
    {
        if ($amount < 20000) {
            return 17;
        } elseif ($amount >= 20000 && $amount < 50000) {
            return 119;
        } elseif ($amount >= 50000 && $amount < 200000) {
            return 240;
        } elseif ($amount >= 200000 && $amount < 1000000) {
            return 510;
        } elseif ($amount >= 1000000) {
            return 1700;
        }
    }

    public function getOrder()
    {
        $countZeros = 7 - mb_strlen($this->id);
        $zeros = '';
        for ($i = 1; $i <= $countZeros; $i++) {
            $zeros = $zeros . '0';
        }
        return 'ЗА-' . $zeros . $this->id;
    }

    public function getPaymentSystem()
    {
        $payment_service = $this->payment_services;
        if ($payment_service == 1) {
            return 'LiqPay';
        } elseif ($payment_service == 2) {
            return 'Безготівковий';
        } elseif ($payment_service == 3) {
            return 'Внутрішня плата за '.$this->getEntity(). ' № '.$this->entity_id;
        } else {
            return $this->payment_service;
        }
    }
    private function getEntity(){
        $entity = $this->entity_type;
        if($entity == 'lot'){
            return 'Лот';
        }elseif($entity == 'tender'){
            return 'Тендер';
        }else{
            return $entity;
        }
    }

    public function getMove()
    {
        $move = $this->move;
        if ($move == 0) {
            return 'Поповнення';
        } elseif ($move == 2) {
            return 'Повернення';
        } else {
            return $this->move;
        }
    }

    public function getStatus()
    {
        $status = $this->status_ps;
        if($this->payment_services == 2 ) {

            if ($status == 'completed') {
                return '<p style="color:green">Завершено</p>';
            } elseif ($status == 'pending') {
                return '<p style="color: sandybrown">Тимчасово заблоковано</p>';
            } elseif ($status == 'cenceled') {
                return '<p style="color:red">Відмінено</p>';
            } else {
                return $this->status;
            }
        }
        if($this->payment_services == 1){
            if ($status == 'success') {
                return '<p style="color:green">Успиішно</p>';
            }
            if ($status == 'failure') {
                return '<p style="color:red">Неуспішно</p>';
            }
            if ($status == 'sandbox') {
                return '<p style="color:red">Тестовий</p>';
            } else {
                return '<p style="color: sandybrown">Тимчасово заблоковано</p>';
            }
        }

        if($this->payment_services == 3){
            if ($status == 'success') {
                return '<p style="color:green">Успиішно</p>';
            }
            if ($status == 'failure') {
                return '<p style="color:red">Неуспішно</p>';
            }
            if ($status == 'sandbox') {
                return '<p style="color:red">Тестовий</p>';
            } else {
                return '<p style="color: sandybrown">Тимчасово заблоковано</p>';
            }
        }

    }
}
