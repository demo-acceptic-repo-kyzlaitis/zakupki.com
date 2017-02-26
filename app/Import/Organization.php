<?php

namespace App\Import;

use App\Model\Country;

class Organization
{
    protected $_data;

    public static $source = 1;


    public static function getModel($data, $includeSource = null)
    {
        if ($includeSource !== 'null') {
            $organization = \App\Model\Organization::getByInaHash($data['identifier']['id'], $includeSource);
        } else {
            $organization = \App\Model\Organization::getByInaHash($data['identifier']['id']);
        }

        $regionId = 0;
        if(isset($data['address']['region'])) {
            $regionName = str_replace('область', '', $data['address']['region']);
            $region     = \App\Model\TendersRegions::orWhere('region_ua', 'LIKE', '%' . $regionName . '%')->orWhere('region_search', 'LIKE', '%' . $regionName . '%')->first();
            if($region) {
                $regionId = $region->id;
            }
        }
        if(isset($data['kind'])) {
            $kind   = \App\Model\Kind::where('kind', $data['kind'])->first();
            $kindId = $kind->id;
        } else {
            $kindId = 0;
        }

        $country = Country::where('country_name_ua', $data['address']['countryName'])->first();
        $organizationData = [
            'name'           => $data['name'],
            'identifier'     => null,
            'country_id'     => ($country) ? $country->id : 228, //228 - id Украины
            'kind_id'        => $kindId,
            'region_name'    => !empty($data['address']['region']) ? $data['address']['region'] : '',
            'region_id'      => $regionId,
            'postal_code'    => isset($data['address']['postalCode']) ? $data['address']['postalCode'] : '',
            'street_address' => isset($data['address']['streetAddress']) ? $data['address']['streetAddress'] : '',
            'locality'       => isset($data['address']['locality']) ? $data['address']['locality'] : '',
            'contact_name'   => $data['contactPoint']['name'],
            'contact_phone'  => isset($data['contactPoint']['telephone']) ? $data['contactPoint']['telephone'] : '',
            'contact_email'  => isset($data['contactPoint']['email']) ? $data['contactPoint']['email'] : '',
            'ina_hash'       => hashFromArray([
                $data['identifier']['id'],
                $data['name'],
                self::_getAddress($data),
            ])
        ];

        if($organization) {
            $organization->update($organizationData);
        } else {
            $organizationData['source']  = 1;
            $organizationData['user_id'] = 0;

            $organization = new \App\Model\Organization($organizationData);
            $organization->save();

            $scheme                  = \App\Model\Identifier::where('scheme', $data['identifier']['scheme'])->first();

            $organizationIdentifiers = new \App\Model\IdentifierOrganization([
                'organisation_id' => $organization->id,
                'identifier_id'   => ($scheme) ? $scheme->id : 0,
                'identifier'      => $data['identifier']['id'],
            ]);
            $organizationIdentifiers->save();
        }

        if($organization) {

            return $organization;
        }

        return false;
    }

    public static function _getAddress($data) {
        $address = [];
        if (!empty($data['address']['postalCode'])) $address[] = $data['address']['postalCode'];
        $address[] = $data['address']['countryName'];
        if (!empty($data['address']['region'])) $address[] = $data['address']['region'];
        if (!empty($data['address']['locality'])) $address[] = $data['address']['locality'];
        if (!empty($data['address']['streetAddress'])) $address[] = $data['address']['streetAddress'];

        return implode(', ', $address);
    }
}
