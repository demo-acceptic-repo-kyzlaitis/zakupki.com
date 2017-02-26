<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source',
        'name',
        'legal_name',
        'legal_name_en',
        'type',
        'identifier',
        'country_id',
        'region_id',
        'kind_id',
        'locality',
        'confirmed',
        'region_name',
        'mode',
        'postal_code',
        'street_address',
        'contact_name',
        'contact_name_en',
        'contact_phone',
        'contact_email',
        'contact_fax',
        'contact_url',
        'contact_available_lang',
        'sign',
        'signed_json',
        'ina_hash',
    ];

    protected $guarded = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function agents() {
        return $this->hasMany('App\Model\Agent', 'organization_id');
    }

    public static $messages = [

    ];

    public static $validation = [
        'source'         => '',
        'name'           => '',
        'type'           => '',
        'identifier'     => '',
        'country_id'     => '',
        'region_id'      => '',
        'locality'       => '',
        'postal_code'    => '',
        'street_address' => '',
        'contact_name'   => '',
        'contact_phone'  => '',
        'contact_email'  => '',
        'contact_fax'    => '',
        'contact_url'    => '',
    ];

    public function organization()
    {
        return $this->belongsTo('App\Model\Organization', 'id');
    }

    public function awards()
    {
        return $this->hasMany('\App\Model\Award');
    }

    public function user()
    {
        return $this->belongsTo('\App\Model\User');
    }

    public function country()
    {
        return $this->belongsTo('\App\Model\Country');
    }

    public function tenders()
    {
        return $this->hasMany('\App\Model\Tender');
    }

    public function plans()
    {
        return $this->hasMany('\App\Model\Plan');
    }

    public function bids()
    {
        return $this->hasMany('\App\Model\Bid');
    }

    public function complaints()
    {
        return $this->hasMany('\App\Model\Complaint');
    }

    public function myQuestions()
    {
        return $this->hasMany('\App\Model\Question', 'organization_to_id');
    }
    public function contacts()
    {
        return $this->hasMany('\App\Model\Contacts', 'organization_id');
    }

    public function activeComplaints()
    {
        if ($this->type == 'customer') {
            return $this->hasMany('\App\Model\Complaint', 'tender_organization_id', 'id')->where('status', 'claim');
        } else {
            return $this->hasMany('\App\Model\Complaint')->where('status', 'answered');
        }
    }


    public function region() {
        return $this->hasOne('\App\Model\TendersRegions', 'id', 'region_id');
    }

    public function kind()
    {
        return $this->hasOne('\App\Model\Kind', 'id', 'kind_id');
    }

    public function getAddress()
    {
        $address = [];
        if (!empty($this->postal_code)) $address[] = $this->postal_code;
        $address[] = $this->country->country_name_ua;
        if (!empty($this->region_name)) $address[] = $this->region_name;
        if (!empty($this->locality)) $address[] = $this->locality;
        if (!empty($this->street_address)) $address[] = $this->street_address;

        return implode(', ', $address);
    }

    public function scopeOur($query)
    {
        return $query->where('source', 0);
    }

    public function scopeNotAnswered($query)
    {
        return $query->where('answer','=','');
    }

    public function scopeIdentifierLike($query, $identifier)
    {
        return $query->select('organizations.*')
            ->join('identifier_organisation', 'organizations.id', '=', 'identifier_organisation.organisation_id')
            ->where('identifier_organisation.identifier', 'LIKE', $identifier);
    }

    public function scopeIdentifier($query, $identifier)
    {
        return $query->select('organizations.*')
            ->join('identifier_organisation', 'organizations.id', '=', 'identifier_organisation.organisation_id')
            ->where('identifier_organisation.identifier', $identifier);
    }

    public function identifiers() {
        return $this->hasMany('App\Model\IdentifierOrganization', 'organisation_id', 'id');
    }

    public function identifiersScheme() {
        return $this->belongsToMany('App\Model\Identifier', 'identifier_organisation', 'organisation_id', 'identifier_id');
    }

    public function __get($key)
    {
        if ($key == 'identifier')
            return ($this->identifiers()->first()) ? $this->identifiers()->first()->identifier : '';
        elseif ($key == 'identifierScheme')
            return $this->identifiers()->first()->scheme->scheme;
        elseif ($key == 'identifierNum')
            return parent::__get('identifier');
        else
            return parent::__get($key);
    }

    public function isConfirmedApi() {
        return $this->confirmed == 1;
    }

    public static function getByIdentifier($identifier = 0, $source = null) {
        if ($source !== null) {
            $organizationQuery = \App\Model\IdentifierOrganization::where('identifier_organisation.identifier', (string) $identifier);
            $organizationQuery->join('organizations', 'organizations.id', '=', 'identifier_organisation.organisation_id');
            $organizationQuery->where('source', $source);
        } else {
            $organizationQuery = \App\Model\IdentifierOrganization::where('identifier', (string) $identifier);
        }
        $identifierOrganization = $organizationQuery->first();
        return ($identifierOrganization) ? $identifierOrganization->organization : null;
    }

    public static function getByInaHash($hash, $source = null) {

        $organizationQuery = Organization::where('ina_hash', $hash);

        if ($source !== null) {
            $organizationQuery->where('source', $source);
        }

        return $organizationQuery->first() ? $organizationQuery->organization : null;
    }
}
