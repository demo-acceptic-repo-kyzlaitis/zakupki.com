<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IdentifierOrganization extends Model
{

    protected $table = 'identifier_organisation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['organisation_id', 'identifier_id', 'identifier'];

    public function scheme() {
        return $this->belongsTo('App\Model\Identifier', 'identifier_id', 'id');
    }

    public function organization() {
        return $this->belongsTo('App\Model\Organization', 'organisation_id', 'id');
    }

    public function scopeOurOrganization($query) {
        return $query->with(['organization' => function($query){
            $query->where('source', 0);
        }]);
    }

    public function scopeWithSource($query) {
        return $query->with(['organization' => function($query){
            $query->where('source', 1);
        }]);
    }

}
