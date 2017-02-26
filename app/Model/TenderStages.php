<?php

    namespace App\Model;

    use Illuminate\Database\Eloquent\Model;

    class TenderStages extends Model {

        /**
         * Model`s table name
         *
         * @var string
         */
        protected $table = 'tender_stages';

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'first_stage',
            'second_stage'
        ];

        /**
         * get first stage of tender
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function firstStage() {
            return $this->hasOne('\App\Model\Tender', 'cbd_id', 'first_stage');
        }

        /**
         * get second stage of tender
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function secondStage() {
            return $this->hasOne('\App\Model\Tender', 'cbd_id', 'second_stage');
        }

    }
