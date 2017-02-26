<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TenderClassification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tender_classification';

    public $timestamps = false;

    public function tender()
    {
        return $this->belongsTo('\App\Model\Tender');
    }

    public function classifier()
    {
        return $this->belongsTo('\App\Model\Classifier');
    }
}
