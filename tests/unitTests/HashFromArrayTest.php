<?php

namespace App\Services\NotificationService\Tests;

use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Support\Facades\DB;

class HashFromArray extends \TestCase
{
    public function testGetHashFromValidData() {

        $inputData = [
            '8998332',
            'І виріс я на чужині, І сивію в чужому краї:
            То одинокому мені Здається — кращого немає
            Нічого в Бога, як Дніпро
            Та наша славная країна...
            Аж бачу, там тілько добро,',
            'Срака міраб 42б Київ',
        ];

        $hash = hashFromArray($inputData);

        $this->assertNotEmpty($hash, $hash);
    }
}