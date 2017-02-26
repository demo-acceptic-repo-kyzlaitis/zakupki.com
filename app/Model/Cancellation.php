<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{
    protected $fillable =[
        'tender_id',
        'reason',
        'date',
        'status',
        'cbd_id',
    ];

    public function status(){
        return $this->belongsTo('\App\Model\Status');
    }
    public function documents(){
        return $this->hasMany('\App\Model\CancellationDocuments');
    }

    public function tender()
    {
        if ($this->cancelable->type == 'tender') {
            return $this->cancelable();
        } else {
            return $this->cancelable->tender();
        }
    }

    public function lot()
    {
        return $this->cancelable()->tender();
    }


    public function cancelable()
    {
        return $this->morphTo();
    }
}
