<?php
/**
 * Created by PhpStorm.
 * User: illia
 * Date: 9/6/2016
 * Time: 7:20 PM
 */

namespace App\Events;


use Illuminate\Queue\SerializesModels;

class TargetNotificationEvent
{
    use SerializesModels;
    public $query;
    public $data;

    /**
     * TargetNotificationEvent constructor.
     * @param $query
     * @param $data
     */
    public function __construct($query, $data) {
        $this->query = $query;
        $this->data = $data;
    }
}