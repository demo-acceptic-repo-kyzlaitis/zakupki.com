<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Classifiers extends Model
{

    const OLD_CLASSIFIER = 2;

    const WRONG_CLASSIFIER = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classifiers';

    public $timestamps = false;

    /**
     * @return array
     */
    public function getNewClassifiersList()
    {
        //todo delete wrong/old classifiers
        return $this->where('status', 1)->whereNotIn('id', [self::OLD_CLASSIFIER, self::WRONG_CLASSIFIER])->lists('name', 'id');
    }

    /**
     * @return array
     */
    public function getClassifiersList()
    {
        return $this->where('status', 1)->whereNotIn('id', [self::WRONG_CLASSIFIER])->lists('name', 'id');
    }
}
