<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    public $table = 'history';

    public $fillable = [
        'alias',
        'field_name',
        'field_value'
    ];

    public function historyable() {
        return $this->morphTo();
    }
}
