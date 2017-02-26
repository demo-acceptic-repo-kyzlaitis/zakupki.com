<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Model\Country;
use App\Model\Organization;
use App\Model\Status;
use App\Model\TendersRegions;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    function __construct()
    {
        if (! Auth()->user()->has_role('business'))
            abort(403);
    }

    /**
     * Display a listing of the organizations.
     *
     * @return Response
     */
    public function organizations(Request $request) {
        $data = $request->all();
        $form = [
            'mode' => '',
            'phone' => '',
            'name' => '',
            'email' => '',
            'legalName' => '',
            'postalCode' => '',
            'country' => '',
            'street' => '',
            'region' => '',
            'start_add_date' => '',
            'end_add_date' => '',
            'classifier' => '',

//            'bid_count_from' => '',
//            'bid_count_to' => '',
//            'tender_id' => '',
        ];
        $regions = TendersRegions::get()->pluck('region_ua', 'id')->all();
        $countries = Country::get()->pluck('country_name_ua', 'id')->all();
        $organizations = collect();
        if (isset($data['form'])) {
            $form = $data['form'];

            $organizations = new Organization();

//            if (!empty($form['bid_count_from']) || !empty($form['bid_count_to'])) {
//                $organizations = $organizations->select('organizations.*', 'bids.id as bids_count')
//                    ->leftJoin('bids', 'organizations.id', '=', 'bids.organization_id')
//                    ->groupBy('organization_id.tender_id')
//                    ->groupBy('bids.tender_id');
//                if (!empty($form['bid_count_from']))
//                    $organizations = $organizations->having('bids_count', '>=', (int) $form['bid_count_from']);
//                if (!empty($form['bid_count_to']))
//                    $organizations = $organizations->having('bids_count', '<=', (int) $form['bid_count_from']);
//            }

            if (strlen($form['mode']) > 0)
                $organizations = $organizations->where('organizations.mode', (int) $form['mode']);
            if (!empty($form['phone']))
                $organizations = $organizations->where('organizations.contact_phone', $form['phone']);
            if (!empty($form['name']))
                $organizations = $organizations->where('organizations.contact_name', 'LIKE', '%' . $form['name'] . '%');
            if (!empty($form['email']))
                $organizations = $organizations->where('organizations.contact_email', $form['email']);
            if (!empty($form['legalName']))
                $organizations = $organizations->where('organizations.name', 'LIKE', '%' . $form['legalName'] . '%');
            if (!empty($form['postalCode']))
                $organizations = $organizations->where('organizations.postal_code', $form['postalCode']);
            if (!empty($form['country']))
                $organizations = $organizations->where('organizations.country_id', (int) $form['country']);
            if (!empty($form['street']))
                $organizations = $organizations->where('organizations.street_address', 'LIKE', '%' . $form['street'] . '%');
            if (!empty($form['region']))
                $organizations = $organizations->where('organizations.region_id', (int) $form['region']);
            if (!empty($form['start_add_date']))
                $organizations = $organizations->where('organizations.created_at', '>=', Carbon::parse($form['start_add_date'])->format('Y-m-d H:i:s'));
            if (!empty($form['end_add_date']))
                $organizations = $organizations->where('organizations.created_at', '<=', Carbon::parse($form['end_add_date'])->format('Y-m-d H:i:s'));
            if (!empty($form['classifier']))
                $organizations = $organizations->select('organizations.*', 'identifier_organisation.identifier_id', 'identifier_organisation.identifier')->join('identifier_organisation', 'organizations.id', '=', 'identifier_organisation.organisation_id')->where('identifier_organisation.identifier', $form['classifier']);

            $organizations = $organizations->paginate(20);
        }

        return view('admin.pages.stats.organizations', compact('organizations', 'form', 'bidStatus', 'regions', 'countries'));
    }
}
