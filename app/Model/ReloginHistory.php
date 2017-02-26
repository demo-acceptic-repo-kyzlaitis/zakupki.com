<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReloginHistory extends Model
{
    protected $table = 'relogin_history';

    public static function setEvent($nomUser,$toUser,$action = null , $entity = null){
        $rh = new ReloginHistory();
        $rh->nominal_user = $nomUser;
        $rh->to_user = $toUser;
        $rh->action = $action;
        $rh->entity = $entity;
        $rh->save();

}
}
