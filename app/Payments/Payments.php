<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 10.06.2016
 * Time: 2:53
 */

namespace App\Payments;


use App\Model\User;
use App\Model\UserBalance;
use Illuminate\Support\Facades\Auth;

class Payments
{
    public static function getPrice($amount)
    {
        if ($amount <= 20000) {
            return 17;
        } elseif ($amount > 20000 && $amount <= 50000) {
            return 119;
        } elseif ($amount > 50000 && $amount <= 200000) {
            return 340;
        } elseif ($amount > 200000 && $amount <= 1000000) {
            return 510;
        } elseif ($amount > 1000000) {
            return 1700;
        }
    }

    public static function balance($userId = null)
    {
        if (is_null($userId)) {
            $user = Auth::user();
        } else {
            $user = User::find($userId);
        }

        $balance = $user->balance;
        if (is_null($balance)) {
            $balance = new UserBalance();
            $user->balance()->save($balance);
        }

        return $balance;
    }

    public static function plus($amount, $userId = null)
    {
        return self::balance($userId)->plus($amount, 3);
    }

    public static function minus($amount, $payment, $userId = null)
    {
        return self::balance($userId)->minus($amount, $payment);
    }
}