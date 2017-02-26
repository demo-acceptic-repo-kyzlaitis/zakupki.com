<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserBalance extends Model
{
    protected $table = 'user_balance';

    public function setAmountAttribute($value) {
        $this->attributes['amount'] = round($value * 100);
    }

    public function getAmountAttribute($value) {
        return number_format($value / 100, 2, '.', '');
    }

    public function user()
    {
        return $this->belongsTo('App\Model\User');
    }

    public function plus($amount, $serviceId = 1, array $additionalParameters = null, $productId = 1) {
        try {
            DB::beginTransaction();
            $this->amount += $amount;
            $this->save();

            $transaction                     = new Transaction();
            $transaction->payment_service_id = $serviceId;
            $transaction->products_id        = $productId;
            $transaction->user_id            = $this->user->id;
            $transaction->amount             = $amount;
            $transaction->balance            = $this->amount;

            if($additionalParameters !== null) {
                $transaction->comment = !empty($additionalParameters['comment']) ? $additionalParameters['comment'] : null;
                $transaction->date_transaction = !empty($additionalParameters['date_transaction']) ? $additionalParameters['date_transaction'] : null;
            }

            DB::commit();

            return $transaction->save();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function minus($amount, $payment = null ,$additionalParameters = null,$serviceId = 3,$productId = 2)
    {
        try {
            DB::beginTransaction();
            $this->amount -= $amount;
            $this->save();

            $transaction = new Transaction();
            $transaction->payment_type = is_object($payment) ? get_class($payment) : '';
            $transaction->payment_service_id = $serviceId;
            $transaction->payment_id = isset($payment->id) ?$payment->id : 0;
            $transaction->comment = isset($additionalParameters['comment']) ? $additionalParameters['comment'] : null;
            $transaction->products_id = $productId;
            $transaction->user_id = $this->user->id;
            $transaction->amount = -1 * $amount;
            $transaction->balance = $this->amount;
            $transaction->save();

            DB::commit();

            return $transaction->save();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
