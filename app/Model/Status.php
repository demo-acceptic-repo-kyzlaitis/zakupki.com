<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'namespace',
        'status',
        'description',
    ];

    public function bids(){
        return $this->hasMany('\App\Model\Bid');
    }

    public function qualification()
    {
        return $this->hasMany('\App\Model\Qualification');
    }

    public function cancellations(){
        return $this->hasMany('\App\Model\Cancellation');
    }

    public function complaints(){
        return $this->hasMany('\App\Model\Complaint');
    }

    public function contracts(){
        return $this->hasMany('\App\Model\Contract');
    }

    /**
     * @param string $namespace
     *
     * @return array
     */
    public function getAllStatuses($namespace = null)
    {
        $statuses = Status::where('namespace', $namespace)->get();
        foreach ($statuses as $status) {
            $result[$status->status] = $status->description;
        }
        return !empty($result) ? $result : [];
    }
}
