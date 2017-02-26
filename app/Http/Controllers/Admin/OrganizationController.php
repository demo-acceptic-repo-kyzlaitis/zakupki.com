<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\CreateOrganizationRequest;
use App\Model\Contacts;
use App\Model\Country;
use App\Model\Identifier;
use App\Model\IdentifierOrganization;
use App\Model\Languages;
use App\Model\Organization;
use App\Model\TendersRegions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OrganizationController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $form = $request->get('form');
        if (empty($form)) {
            $form = [
                'id'         => '',
                'type'       => '',
                'mode'       => '',
                'start_date' => '',
                'end_date'   => '',
                'name'       => '',
                'code'       => '',
                'email'      => '',
                'phone'      => '',
                'source'     => '',
            ];
        }
//        $organizations = Organization::where('source', '0')->where(function($query) use ($form) {
//            $query->orWhere('name', 'LIKE', "%{$form['']}%");
//            $query->orWhere('identifier', 'LIKE', '%'.$searchQuery.'%');
//        })->paginate(20);

        if(empty($form['source'])) { //если поле "Площадка"  не указана (Просто нажали кнопку поиск)
            $query = Organization::where('user_id', '>', 0)->where(function($query) use ($form) { // условие user_id > 2 and (source = 0 or source = 2) and ..
                $query->orWhere('source', 2);
                $query->orWhere('source', 0);
            });
        } else { // если указали
            $query = Organization::where('user_id', '>', 0)->where('source', (int)explode('_', $form['source'])[1]);
        }


        if ($form['start_date']) {
            $query->where('created_at', '>', Carbon::parse($form['start_date']));
        }
        if ($form['end_date']) {
            $query->where('created_at', '<', Carbon::parse($form['end_date']));
        }
        if (!empty($form['type'])) {
            $query->where('type', $form['type']);
        }
        if (!empty($form['mode'])) {
            $query->where('mode', $form['mode']);
        }
        if (!empty($form['name'])) {
            $query->where('name', 'LIKE', "%{$form['name']}%");
        }
        if (!empty($form['id'])) {
            $query->where('id', $form['id']);
        }
        if (!empty($form['code'])) {
            $query->identifierLike("%{$form['code']}%");
        }
        if (!empty($form['email'])) {
            $query->where('contact_email', 'LIKE', "%{$form['email']}%");
        }
        if (!empty($form['phone'])) {
            $query->where(function($query) use ($form) {
                $query->orWhere('contact_name', 'LIKE', "%{$form['phone']}%");
                $query->orWhere('contact_phone', 'LIKE', "%{$form['phone']}%");
            });
        }
        
        $organizations = $query->paginate(20);
        return view('admin.pages.organization.list', compact('organizations', 'form'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $organization = Auth::user()->organization;
        if ($organization) {

            return redirect()->route('organization.edit');
        }
        $regions = \App\Model\TendersRegions::orderBy('region_name')->lists('region_name', 'id');
        $schemes = groupByValue(Identifier::active()->lists('country_iso', 'scheme'));

        return view('pages.organization.create', compact('regions', 'schemes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function store(CreateOrganizationRequest $request)
    {
        $organization = Auth::user()->organization;
        if ($organization) {

            return redirect()->route('organization.edit');
        }

        Auth::user()->organization()->save(new Organization($request->all()));

        return redirect()->route('organization.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        $organization = Organization::find($id);
        if(!empty($organization)) {
            if ($organization->contacts()->count() != 0) {
                $contactsAll = $organization->contacts;
            } else {
                $contact = [
                    "organization_id" => $organization->id,
                    "primary" => 1,
                    "contact_name" => $organization->contact_name,
                    "contact_email" => $organization->contact_email,
                    "contact_phone" => $organization->contact_phone,
                    "contact_fax" => $organization->contact_fax,
                    "contact_url" => $organization->contact_url,];
                $contacts = new Contacts($contact);
                $contacts->save();
                $contactsAll = $organization->contacts;
            }
        }
        $regions = \App\Model\TendersRegions::orderBy('region_name')->lists('region_name', 'id');
        $languages = Languages::active()->lists('language_name', 'language_code');
        $countries = Country::active()->lists('country_name_ua', 'country_iso');
        $schemes = groupByValue(Identifier::active()->lists('country_iso', 'scheme'));
        $admin = Auth::user()->super_user;
        return view('admin.pages.organization.edit', compact('organization', 'regions','contactsAll','admin', 'languages', 'countries', 'schemes'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int     $id
     *
     * @return Response
     */
    public function update(CreateOrganizationRequest $request, $id)
    {

        $data = $request->all();

        $data['confirmed'] = 0;
        $data['mode'] = 0;
        $contact = $data['contact'];
        if(isset($data['primary_contact'])){
            $primaryContact = $data['primary_contact'];
        }else{
            $ak = array_keys($contact);
            $primaryContact = $ak[0];
        }
        if ($data['deleted_ids'] != '0'){
            $pieces = explode(",", $data['deleted_ids']);
            foreach ($pieces as $del){
                $cont = Contacts::find($del);
                if ($cont != null ){
                    $cont->delete();
                }

            }
        }

        $country = Country::where('country_iso', $data['country_iso'])->first();
        if ($country) {
            $data["country_id"] = $country->id;
        } else {
            Session::flash('flash_error', 'Виберіть країну зі списку.');
            return redirect()->withInput()->back();
        }
        if ($data['country_iso'] == 'UA') {
            $region = TendersRegions::find($data["region_id"]);
            if ($region)
                $data["region_name"] = $region->region_ua;
            $scheme = Identifier::where('scheme', 'UA-EDR')->first();
        } else {
            $data["region_id"] = 0;
            $data["region_name"] = '';
            $scheme = $country->identifiers()->first();
        }

        $contact[$primaryContact]['primary'] = 1;
        unset($data['contact']);
        foreach ($contact as $key => $cont) {
            $cont['organization_id'] = $id;
            if (isset($cont['primary']) && $cont['primary'] == 1) {
                $data["contact_name"] = $cont['contact_name'];
                $data["contact_phone"] = $cont['contact_phone'];
                $data["contact_email"] = $cont['contact_email'];
                $data["contact_url"] = (isset($cont['contact_url'])) ? $cont['contact_url'] : null;
                $contactModel = Contacts::find($key);

                if ($contactModel != null) {
                    $contactModel->update($cont);
                    $contactModel->save();
                } else {
                    $cm = new Contacts($cont);
                    $cm->save();

                }
            }else {
                $contactModel = Contacts::find($key);
                if ($contactModel != null) {
                    $contactModel->contact_name = $cont['contact_name'];
                    $contactModel->contact_phone = $cont['contact_phone'];
                    $contactModel->contact_email = $cont['contact_email'];
                    $contactModel->contact_url = (isset($cont['contact_url'])) ? $cont['contact_url'] : null;
                    $contactModel->primary = "0";
                    $contactModel->save();
                } else {
                    $cm = new Contacts($cont);
                    $cm->save();
                }
            }
        }

        $organization = Organization::find($id);
        $organizationIdentifiers = IdentifierOrganization::where('organisation_id', $organization->id)->first();
        if ($organizationIdentifiers) {
            $organizationIdentifiers->update([
                'identifier_id' => (isset($scheme)) ? $scheme->id : 0,
                'identifier' => $data["organization_identifier"]
            ]);
        } else {
            $organizationIdentifiers = new IdentifierOrganization([
                'organisation_id' => $organization->id,
                'identifier_id' => (isset($scheme)) ? $scheme->id : 0,
                'identifier' => $data["organization_identifier"]
            ]);
            $organizationIdentifiers->save();
        }

        $organization->update($data);
        session()->flash('flash_message', 'Данные обновлены успешно.');

        return redirect()->route('admin::organization.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id) {
        //
    }

    public function confirm(Request $request, $id)
    {
        $organization = Organization::find($id);
        if ($organization->confirmed) {
            $organization->confirmed = 0;
            $organization->mode = 0;
        } else {
            $organization->confirmed = 1;
        }

        $organization->save();
        Session::flash('flash_message', 'Організація підтверджена');

        return redirect()->back();
    }


    /**
     * @param Request $request
     * @param $id organization id
     */
    public function checkSign(Request $request, $id) {
        $organization = Organization::find($id);

        if($organization === null) {
            Session::flash('flash_error', 'Організація не знайдена');
            return redirect()->back();
        }

        //$data = '{"kind":"general","name":"\u041e\u0440\u0434\u0435\u043d\u0430 \"\u0417\u043d\u0430\u043a \u041f\u043e\u0448\u0430\u043d\u0438\" \u0432\u0438\u0449\u0435 \u043f\u0440\u043e\u0444\u0435\u0441\u0456\u0439\u043d\u0435 \u0443\u0447\u0438\u043b\u0438\u0449\u0435 \u2116 75","identifier":{"scheme":"","id":"02541823","legalName":"\u041e\u0440\u0434\u0435\u043d\u0430 \"\u0417\u043d\u0430\u043a \u041f\u043e\u0448\u0430\u043d\u0438\" \u0432\u0438\u0449\u0435 \u043f\u0440\u043e\u0444\u0435\u0441\u0456\u0439\u043d\u0435 \u0443\u0447\u0438\u043b\u0438\u0449\u0435 \u2116 75","url":""},"address":{"UTregionid":6,"countryName":"\u0423\u043a\u0440\u0430\u0457\u043d\u0430","locality":"\u041e\u043b\u0435\u043a\u0441\u0430\u043d\u0434\u0440\u0456\u0432\u043a\u0430","region":"\u0414\u041d\u0406\u041f\u0420\u041e\u041f\u0415\u0422\u0420\u041e\u0412\u0421\u042c\u041a\u0410 \u041e\u0411\u041b\u0410\u0421\u0422\u042c","streetAddress":"\u0428\u043a\u0456\u043b\u044c\u043d\u0430","postalCode":"53630"},"contactPoint":{"name":"\u041a\u0430\u0440\u0430\u0441\u0454\u0432\u0438\u0447 \u0404\u0432\u0433\u0435\u043d\u0456\u044f \u0412\u043e\u043b\u043e\u0434\u0438\u043c\u0438\u0440\u0456\u0432\u043d\u0430","email":"ya.buhvpu@ukr.net","telephone":"0563822279","faxNumber":"0563822279"},"regname":"02541823","regemail":"ya.buhvpu@ukr.net","regpassword":"$2a$10$1evWj9KmlOvNBiwfca5R3u3qUdN4vDMDgrwEHqLu.vQC5vRTF0Fqu","sign":"MIIViwYJKoZIhvcNAQcCoIIVfDCCFXgCAQExDjAMBgoqhiQCAQEBAQIBMIIFiQYJKoZIhvcNAQcBoIIFegSCBXZ7ImtpbmQiOiJnZW5lcmFsIiwibmFtZSI6Ilx1MDQxRVx1MDQ0MFx1MDQzNFx1MDQzNVx1MDQzRFx1MDQzMCBcIlx1MDQxN1x1MDQzRFx1MDQzMFx1MDQzQSBcdTA0MUZcdTA0M0VcdTA0NDhcdTA0MzBcdTA0M0RcdTA0MzhcIiBcdTA0MzJcdTA0MzhcdTA0NDlcdTA0MzUgXHUwNDNGXHUwNDQwXHUwNDNFXHUwNDQ0XHUwNDM1XHUwNDQxXHUwNDU2XHUwNDM5XHUwNDNEXHUwNDM1IFx1MDQ0M1x1MDQ0N1x1MDQzOFx1MDQzQlx1MDQzOFx1MDQ0OVx1MDQzNSBcdTIxMTYgNzUiLCJpZGVudGlmaWVyIjp7InNjaGVtZSI6IiIsImlkIjoiMDI1NDE4MjMiLCJsZWdhbE5hbWUiOiJcdTA0MUVcdTA0NDBcdTA0MzRcdTA0MzVcdTA0M0RcdTA0MzAgXCJcdTA0MTdcdTA0M0RcdTA0MzBcdTA0M0EgXHUwNDFGXHUwNDNFXHUwNDQ4XHUwNDMwXHUwNDNEXHUwNDM4XCIgXHUwNDMyXHUwNDM4XHUwNDQ5XHUwNDM1IFx1MDQzRlx1MDQ0MFx1MDQzRVx1MDQ0NFx1MDQzNVx1MDQ0MVx1MDQ1Nlx1MDQzOVx1MDQzRFx1MDQzNSBcdTA0NDNcdTA0NDdcdTA0MzhcdTA0M0JcdTA0MzhcdTA0NDlcdTA0MzUgXHUyMTE2IDc1IiwidXJsIjoiIn0sImFkZHJlc3MiOnsiVVRyZWdpb25pZCI6NiwiY291bnRyeU5hbWUiOiJcdTA0MjNcdTA0M0FcdTA0NDBcdTA0MzBcdTA0NTdcdTA0M0RcdTA0MzAiLCJsb2NhbGl0eSI6Ilx1MDQxRVx1MDQzQlx1MDQzNVx1MDQzQVx1MDQ0MVx1MDQzMFx1MDQzRFx1MDQzNFx1MDQ0MFx1MDQ1Nlx1MDQzMlx1MDQzQVx1MDQzMCIsInJlZ2lvbiI6Ilx1MDQxNFx1MDQxRFx1MDQwNlx1MDQxRlx1MDQyMFx1MDQxRVx1MDQxRlx1MDQxNVx1MDQyMlx1MDQyMFx1MDQxRVx1MDQxMlx1MDQyMVx1MDQyQ1x1MDQxQVx1MDQxMCBcdTA0MUVcdTA0MTFcdTA0MUJcdTA0MTBcdTA0MjFcdTA0MjJcdTA0MkMiLCJzdHJlZXRBZGRyZXNzIjoiXHUwNDI4XHUwNDNBXHUwNDU2XHUwNDNCXHUwNDRDXHUwNDNEXHUwNDMwIiwicG9zdGFsQ29kZSI6IjUzNjMwIn0sImNvbnRhY3RQb2ludCI6eyJuYW1lIjoiXHUwNDFBXHUwNDMwXHUwNDQwXHUwNDMwXHUwNDQxXHUwNDU0XHUwNDMyXHUwNDM4XHUwNDQ3IFx1MDQwNFx1MDQzMlx1MDQzM1x1MDQzNVx1MDQzRFx1MDQ1Nlx1MDQ0RiBcdTA0MTJcdTA0M0VcdTA0M0JcdTA0M0VcdTA0MzRcdTA0MzhcdTA0M0NcdTA0MzhcdTA0NDBcdTA0NTZcdTA0MzJcdTA0M0RcdTA0MzAiLCJlbWFpbCI6InlhLmJ1aHZwdUB1a3IubmV0IiwidGVsZXBob25lIjoiMDU2MzgyMjI3OSIsImZheE51bWJlciI6IjA1NjM4MjIyNzkifSwicmVnbmFtZSI6IjAyNTQxODIzIiwicmVnZW1haWwiOiJ5YS5idWh2cHVAdWtyLm5ldCIsInJlZ3Bhc3N3b3JkIjoiJDJhJDEwJDFldldqOUttbE92TkJpd2ZjYTVSM3UzcVVkTjR2RE1EZ3J3RUhxTHUudlFDNXZSVEYwRnF1In2ggge9MIIHuTCCB2GgAwIBAgIUM7bLe\/chuc4EAAAAyfYbABweOgAwDQYLKoYkAgEBAQEDAQEwggFQMVQwUgYDVQQKDEvQhtC90YTQvtGA0LzQsNGG0ZbQudC90L4t0LTQvtCy0ZbQtNC60L7QstC40Lkg0LTQtdC\/0LDRgNGC0LDQvNC10L3RgiDQlNCk0KExXjBcBgNVBAsMVdCj0L\/RgNCw0LLQu9GW0L3QvdGPICjRhtC10L3RgtGAKSDRgdC10YDRgtC40YTRltC60LDRhtGW0Zcg0LrQu9GO0YfRltCyINCG0JTQlCDQlNCk0KExYjBgBgNVBAMMWdCQ0LrRgNC10LTQuNGC0L7QstCw0L3QuNC5INGG0LXQvdGC0YAg0YHQtdGA0YLQuNGE0ZbQutCw0YbRltGXINC60LvRjtGH0ZbQsiDQhtCU0JQg0JTQpNChMRQwEgYDVQQFDAtVQS0zOTM4NDQ3NjELMAkGA1UEBhMCVUExETAPBgNVBAcMCNCa0LjRl9CyMB4XDTE1MTEwMzIyMDAwMFoXDTE3MTEwMzIyMDAwMFowggEmMWEwXwYDVQQKDFjQntCg0JTQldCd0JAgItCX0J3QkNCaINCf0J7QqNCQ0J3QmCIg0JLQmNCp0JUg0J\/QoNCe0KTQldCh0IbQmdCd0JUg0KPQp9CY0JvQmNCp0JUg4oSWIDc1MRYwFAYDVQQLDA3QktCf0KMg4oSWIDc1MTowOAYDVQQDDDHQtdC70LXQutGC0YDQvtC90L3QsCDQv9C10YfQsNGC0LrQsCDQktCf0KMg4oSWIDc1MRAwDgYDVQQFDAcxODMyNjQ5MQswCQYDVQQGEwJVQTEjMCEGA1UEBwwa0J7Qu9C10LrRgdCw0L3QtNGA0ZbQstC60LAxKTAnBgNVBAgMINCU0L3RltC\/0YDQvtC\/0LXRgtGA0L7QstGB0YzQutCwMIHyMIHJBgsqhiQCAQEBAQMBATCBuTB1MAcCAgEBAgEMAgEABCEQvuPbauqeH4ZXjEXBJZT\/lCOUp9c4+Rh+ZRUBcpT0zgECIQCAAAAAAAAAAAAAAAAAAAAAZ1khOvGC6YfT4XcUkH1HDQQhtg\/S2NzoqTQjxhAbypHEegB+bDALJs1VbJsOfSDvKSoABECp1utF8TxwgoDElnsjH16t9ljrpMA3KR042WvwJcpOF\/jpcg3GFbQ6KJdfC8Heo2Q4tWTqLBef0BI+bbj6xXkEAyQABCHckdowv3kz\/IRp61GOUL2vCRiu9qx3+Aw9v+mnLRDrBQCjggOgMIIDnDApBgNVHQ4EIgQgHrztTpm5jN4OlppQJAn0fipdyE0fxGHhNXMgnoSFeMUwKwYDVR0jBCQwIoAgM7bLe\/chuc7u494uYv7qO3AaS2dgvBwvzzVlFrUOvKowLwYDVR0QBCgwJqARGA8yMDE1MTEwMzIyMDAwMFqhERgPMjAxNzExMDMyMjAwMDBaMA4GA1UdDwEB\/wQEAwIGwDAXBgNVHSUBAf8EDTALBgkqhiQCAQEBAwkwGQYDVR0gAQH\/BA8wDTALBgkqhiQCAQEBAgIwDAYDVR0TAQH\/BAIwADA3BggrBgEFBQcBAwEB\/wQoMCYwCwYJKoYkAgEBAQIBMBcGBgQAjkYBAjANEwNVQUgCAw9CQAIBADCB\/QYDVR0RBIH1MIHyoIGdBgwrBgEEAYGXRgEBBAKggYwMgYk1MzYzMCwg0JTQvdGW0L\/RgNC+0L\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"}';
        $data = $organization->signed_json;
        $json = json_decode($data, true);
        $sign = $json['sign'];
        unset($json['sign']);
        $result = dstu_verify(json_encode($json, JSON_UNESCAPED_SLASHES), base64_decode($sign), public_path('js/sign/CACertificates.p7b'));
        if($result === false) {
            Session::flash('flash_error', 'Невірний підпис');
            return redirect()->back();
        } else {
            Session::flash('flash_message', 'єдрпоу з бд ' . $organization->identifier . ' = ' . 'эдрпоу з підписа ' . $result[0]);
            return redirect()->back();
        }
        /**
         * works
         */
        //$data = '{"kind":"general","name":"\u041e\u0440\u0434\u0435\u043d\u0430 \"\u0417\u043d\u0430\u043a \u041f\u043e\u0448\u0430\u043d\u0438\" \u0432\u0438\u0449\u0435 \u043f\u0440\u043e\u0444\u0435\u0441\u0456\u0439\u043d\u0435 \u0443\u0447\u0438\u043b\u0438\u0449\u0435 \u2116 75","identifier":{"scheme":"","id":"02541823","legalName":"\u041e\u0440\u0434\u0435\u043d\u0430 \"\u0417\u043d\u0430\u043a \u041f\u043e\u0448\u0430\u043d\u0438\" \u0432\u0438\u0449\u0435 \u043f\u0440\u043e\u0444\u0435\u0441\u0456\u0439\u043d\u0435 \u0443\u0447\u0438\u043b\u0438\u0449\u0435 \u2116 75","url":""},"address":{"UTregionid":6,"countryName":"\u0423\u043a\u0440\u0430\u0457\u043d\u0430","locality":"\u041e\u043b\u0435\u043a\u0441\u0430\u043d\u0434\u0440\u0456\u0432\u043a\u0430","region":"\u0414\u041d\u0406\u041f\u0420\u041e\u041f\u0415\u0422\u0420\u041e\u0412\u0421\u042c\u041a\u0410 \u041e\u0411\u041b\u0410\u0421\u0422\u042c","streetAddress":"\u0428\u043a\u0456\u043b\u044c\u043d\u0430","postalCode":"53630"},"contactPoint":{"name":"\u041a\u0430\u0440\u0430\u0441\u0454\u0432\u0438\u0447 \u0404\u0432\u0433\u0435\u043d\u0456\u044f \u0412\u043e\u043b\u043e\u0434\u0438\u043c\u0438\u0440\u0456\u0432\u043d\u0430","email":"ya.buhvpu@ukr.net","telephone":"0563822279","faxNumber":"0563822279"},"regname":"02541823","regemail":"ya.buhvpu@ukr.net","regpassword":"$2a$10$1evWj9KmlOvNBiwfca5R3u3qUdN4vDMDgrwEHqLu.vQC5vRTF0Fqu","sign":"MIIViwYJKoZIhvcNAQcCoIIVfDCCFXgCAQExDjAMBgoqhiQCAQEBAQIBMIIFiQYJKoZIhvcNAQcBoIIFegSCBXZ7ImtpbmQiOiJnZW5lcmFsIiwibmFtZSI6Ilx1MDQxRVx1MDQ0MFx1MDQzNFx1MDQzNVx1MDQzRFx1MDQzMCBcIlx1MDQxN1x1MDQzRFx1MDQzMFx1MDQzQSBcdTA0MUZcdTA0M0VcdTA0NDhcdTA0MzBcdTA0M0RcdTA0MzhcIiBcdTA0MzJcdTA0MzhcdTA0NDlcdTA0MzUgXHUwNDNGXHUwNDQwXHUwNDNFXHUwNDQ0XHUwNDM1XHUwNDQxXHUwNDU2XHUwNDM5XHUwNDNEXHUwNDM1IFx1MDQ0M1x1MDQ0N1x1MDQzOFx1MDQzQlx1MDQzOFx1MDQ0OVx1MDQzNSBcdTIxMTYgNzUiLCJpZGVudGlmaWVyIjp7InNjaGVtZSI6IiIsImlkIjoiMDI1NDE4MjMiLCJsZWdhbE5hbWUiOiJcdTA0MUVcdTA0NDBcdTA0MzRcdTA0MzVcdTA0M0RcdTA0MzAgXCJcdTA0MTdcdTA0M0RcdTA0MzBcdTA0M0EgXHUwNDFGXHUwNDNFXHUwNDQ4XHUwNDMwXHUwNDNEXHUwNDM4XCIgXHUwNDMyXHUwNDM4XHUwNDQ5XHUwNDM1IFx1MDQzRlx1MDQ0MFx1MDQzRVx1MDQ0NFx1MDQzNVx1MDQ0MVx1MDQ1Nlx1MDQzOVx1MDQzRFx1MDQzNSBcdTA0NDNcdTA0NDdcdTA0MzhcdTA0M0JcdTA0MzhcdTA0NDlcdTA0MzUgXHUyMTE2IDc1IiwidXJsIjoiIn0sImFkZHJlc3MiOnsiVVRyZWdpb25pZCI6NiwiY291bnRyeU5hbWUiOiJcdTA0MjNcdTA0M0FcdTA0NDBcdTA0MzBcdTA0NTdcdTA0M0RcdTA0MzAiLCJsb2NhbGl0eSI6Ilx1MDQxRVx1MDQzQlx1MDQzNVx1MDQzQVx1MDQ0MVx1MDQzMFx1MDQzRFx1MDQzNFx1MDQ0MFx1MDQ1Nlx1MDQzMlx1MDQzQVx1MDQzMCIsInJlZ2lvbiI6Ilx1MDQxNFx1MDQxRFx1MDQwNlx1MDQxRlx1MDQyMFx1MDQxRVx1MDQxRlx1MDQxNVx1MDQyMlx1MDQyMFx1MDQxRVx1MDQxMlx1MDQyMVx1MDQyQ1x1MDQxQVx1MDQxMCBcdTA0MUVcdTA0MTFcdTA0MUJcdTA0MTBcdTA0MjFcdTA0MjJcdTA0MkMiLCJzdHJlZXRBZGRyZXNzIjoiXHUwNDI4XHUwNDNBXHUwNDU2XHUwNDNCXHUwNDRDXHUwNDNEXHUwNDMwIiwicG9zdGFsQ29kZSI6IjUzNjMwIn0sImNvbnRhY3RQb2ludCI6eyJuYW1lIjoiXHUwNDFBXHUwNDMwXHUwNDQwXHUwNDMwXHUwNDQxXHUwNDU0XHUwNDMyXHUwNDM4XHUwNDQ3IFx1MDQwNFx1MDQzMlx1MDQzM1x1MDQzNVx1MDQzRFx1MDQ1Nlx1MDQ0RiBcdTA0MTJcdTA0M0VcdTA0M0JcdTA0M0VcdTA0MzRcdTA0MzhcdTA0M0NcdTA0MzhcdTA0NDBcdTA0NTZcdTA0MzJcdTA0M0RcdTA0MzAiLCJlbWFpbCI6InlhLmJ1aHZwdUB1a3IubmV0IiwidGVsZXBob25lIjoiMDU2MzgyMjI3OSIsImZheE51bWJlciI6IjA1NjM4MjIyNzkifSwicmVnbmFtZSI6IjAyNTQxODIzIiwicmVnZW1haWwiOiJ5YS5idWh2cHVAdWtyLm5ldCIsInJlZ3Bhc3N3b3JkIjoiJDJhJDEwJDFldldqOUttbE92TkJpd2ZjYTVSM3UzcVVkTjR2RE1EZ3J3RUhxTHUudlFDNXZSVEYwRnF1In2ggge9MIIHuTCCB2GgAwIBAgIUM7bLe\/chuc4EAAAAyfYbABweOgAwDQYLKoYkAgEBAQEDAQEwggFQMVQwUgYDVQQKDEvQhtC90YTQvtGA0LzQsNGG0ZbQudC90L4t0LTQvtCy0ZbQtNC60L7QstC40Lkg0LTQtdC\/0LDRgNGC0LDQvNC10L3RgiDQlNCk0KExXjBcBgNVBAsMVdCj0L\/RgNCw0LLQu9GW0L3QvdGPICjRhtC10L3RgtGAKSDRgdC10YDRgtC40YTRltC60LDRhtGW0Zcg0LrQu9GO0YfRltCyINCG0JTQlCDQlNCk0KExYjBgBgNVBAMMWdCQ0LrRgNC10LTQuNGC0L7QstCw0L3QuNC5INGG0LXQvdGC0YAg0YHQtdGA0YLQuNGE0ZbQutCw0YbRltGXINC60LvRjtGH0ZbQsiDQhtCU0JQg0JTQpNChMRQwEgYDVQQFDAtVQS0zOTM4NDQ3NjELMAkGA1UEBhMCVUExETAPBgNVBAcMCNCa0LjRl9CyMB4XDTE1MTEwMzIyMDAwMFoXDTE3MTEwMzIyMDAwMFowggEmMWEwXwYDVQQKDFjQntCg0JTQldCd0JAgItCX0J3QkNCaINCf0J7QqNCQ0J3QmCIg0JLQmNCp0JUg0J\/QoNCe0KTQldCh0IbQmdCd0JUg0KPQp9CY0JvQmNCp0JUg4oSWIDc1MRYwFAYDVQQLDA3QktCf0KMg4oSWIDc1MTowOAYDVQQDDDHQtdC70LXQutGC0YDQvtC90L3QsCDQv9C10YfQsNGC0LrQsCDQktCf0KMg4oSWIDc1MRAwDgYDVQQFDAcxODMyNjQ5MQswCQYDVQQGEwJVQTEjMCEGA1UEBwwa0J7Qu9C10LrRgdCw0L3QtNGA0ZbQstC60LAxKTAnBgNVBAgMINCU0L3RltC\/0YDQvtC\/0LXRgtGA0L7QstGB0YzQutCwMIHyMIHJBgsqhiQCAQEBAQMBATCBuTB1MAcCAgEBAgEMAgEABCEQvuPbauqeH4ZXjEXBJZT\/lCOUp9c4+Rh+ZRUBcpT0zgECIQCAAAAAAAAAAAAAAAAAAAAAZ1khOvGC6YfT4XcUkH1HDQQhtg\/S2NzoqTQjxhAbypHEegB+bDALJs1VbJsOfSDvKSoABECp1utF8TxwgoDElnsjH16t9ljrpMA3KR042WvwJcpOF\/jpcg3GFbQ6KJdfC8Heo2Q4tWTqLBef0BI+bbj6xXkEAyQABCHckdowv3kz\/IRp61GOUL2vCRiu9qx3+Aw9v+mnLRDrBQCjggOgMIIDnDApBgNVHQ4EIgQgHrztTpm5jN4OlppQJAn0fipdyE0fxGHhNXMgnoSFeMUwKwYDVR0jBCQwIoAgM7bLe\/chuc7u494uYv7qO3AaS2dgvBwvzzVlFrUOvKowLwYDVR0QBCgwJqARGA8yMDE1MTEwMzIyMDAwMFqhERgPMjAxNzExMDMyMjAwMDBaMA4GA1UdDwEB\/wQEAwIGwDAXBgNVHSUBAf8EDTALBgkqhiQCAQEBAwkwGQYDVR0gAQH\/BA8wDTALBgkqhiQCAQEBAgIwDAYDVR0TAQH\/BAIwADA3BggrBgEFBQcBAwEB\/wQoMCYwCwYJKoYkAgEBAQIBMBcGBgQAjkYBAjANEwNVQUgCAw9CQAIBADCB\/QYDVR0RBIH1MIHyoIGdBgwrBgEEAYGXRgEBBAKggYwMgYk1MzYzMCwg0JTQvdGW0L\/RgNC+0L\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"}';
        // trying parus works
        //$data = '{"kind":"general","name":"\u041e\u0440\u0434\u0435\u043d\u0430 \"\u0417\u043d\u0430\u043a \u041f\u043e\u0448\u0430\u043d\u0438\" \u0432\u0438\u0449\u0435 \u043f\u0440\u043e\u0444\u0435\u0441\u0456\u0439\u043d\u0435 \u0443\u0447\u0438\u043b\u0438\u0449\u0435 \u2116 75","identifier":{"scheme":"","id":"02541823","legalName":"\u041e\u0440\u0434\u0435\u043d\u0430 \"\u0417\u043d\u0430\u043a \u041f\u043e\u0448\u0430\u043d\u0438\" \u0432\u0438\u0449\u0435 \u043f\u0440\u043e\u0444\u0435\u0441\u0456\u0439\u043d\u0435 \u0443\u0447\u0438\u043b\u0438\u0449\u0435 \u2116 75","url":""},"address":{"UTregionid":6,"countryName":"\u0423\u043a\u0440\u0430\u0457\u043d\u0430","locality":"\u041e\u043b\u0435\u043a\u0441\u0430\u043d\u0434\u0440\u0456\u0432\u043a\u0430","region":"\u0414\u041d\u0406\u041f\u0420\u041e\u041f\u0415\u0422\u0420\u041e\u0412\u0421\u042c\u041a\u0410 \u041e\u0411\u041b\u0410\u0421\u0422\u042c","streetAddress":"\u0428\u043a\u0456\u043b\u044c\u043d\u0430","postalCode":"53630"},"contactPoint":{"name":"\u041a\u0430\u0440\u0430\u0441\u0454\u0432\u0438\u0447 \u0404\u0432\u0433\u0435\u043d\u0456\u044f \u0412\u043e\u043b\u043e\u0434\u0438\u043c\u0438\u0440\u0456\u0432\u043d\u0430","email":"ya.buhvpu@ukr.net","telephone":"0563822279","faxNumber":"0563822279"},"regname":"02541823","regemail":"ya.buhvpu@ukr.net","regpassword":"$2a$10$1evWj9KmlOvNBiwfca5R3u3qUdN4vDMDgrwEHqLu.vQC5vRTF0Fqu","sign":"MIIViwYJKoZIhvcNAQcCoIIVfDCCFXgCAQExDjAMBgoqhiQCAQEBAQIBMIIFiQYJKoZIhvcNAQcBoIIFegSCBXZ7ImtpbmQiOiJnZW5lcmFsIiwibmFtZSI6Ilx1MDQxRVx1MDQ0MFx1MDQzNFx1MDQzNVx1MDQzRFx1MDQzMCBcIlx1MDQxN1x1MDQzRFx1MDQzMFx1MDQzQSBcdTA0MUZcdTA0M0VcdTA0NDhcdTA0MzBcdTA0M0RcdTA0MzhcIiBcdTA0MzJcdTA0MzhcdTA0NDlcdTA0MzUgXHUwNDNGXHUwNDQwXHUwNDNFXHUwNDQ0XHUwNDM1XHUwNDQxXHUwNDU2XHUwNDM5XHUwNDNEXHUwNDM1IFx1MDQ0M1x1MDQ0N1x1MDQzOFx1MDQzQlx1MDQzOFx1MDQ0OVx1MDQzNSBcdTIxMTYgNzUiLCJpZGVudGlmaWVyIjp7InNjaGVtZSI6IiIsImlkIjoiMDI1NDE4MjMiLCJsZWdhbE5hbWUiOiJcdTA0MUVcdTA0NDBcdTA0MzRcdTA0MzVcdTA0M0RcdTA0MzAgXCJcdTA0MTdcdTA0M0RcdTA0MzBcdTA0M0EgXHUwNDFGXHUwNDNFXHUwNDQ4XHUwNDMwXHUwNDNEXHUwNDM4XCIgXHUwNDMyXHUwNDM4XHUwNDQ5XHUwNDM1IFx1MDQzRlx1MDQ0MFx1MDQzRVx1MDQ0NFx1MDQzNVx1MDQ0MVx1MDQ1Nlx1MDQzOVx1MDQzRFx1MDQzNSBcdTA0NDNcdTA0NDdcdTA0MzhcdTA0M0JcdTA0MzhcdTA0NDlcdTA0MzUgXHUyMTE2IDc1IiwidXJsIjoiIn0sImFkZHJlc3MiOnsiVVRyZWdpb25pZCI6NiwiY291bnRyeU5hbWUiOiJcdTA0MjNcdTA0M0FcdTA0NDBcdTA0MzBcdTA0NTdcdTA0M0RcdTA0MzAiLCJsb2NhbGl0eSI6Ilx1MDQxRVx1MDQzQlx1MDQzNVx1MDQzQVx1MDQ0MVx1MDQzMFx1MDQzRFx1MDQzNFx1MDQ0MFx1MDQ1Nlx1MDQzMlx1MDQzQVx1MDQzMCIsInJlZ2lvbiI6Ilx1MDQxNFx1MDQxRFx1MDQwNlx1MDQxRlx1MDQyMFx1MDQxRVx1MDQxRlx1MDQxNVx1MDQyMlx1MDQyMFx1MDQxRVx1MDQxMlx1MDQyMVx1MDQyQ1x1MDQxQVx1MDQxMCBcdTA0MUVcdTA0MTFcdTA0MUJcdTA0MTBcdTA0MjFcdTA0MjJcdTA0MkMiLCJzdHJlZXRBZGRyZXNzIjoiXHUwNDI4XHUwNDNBXHUwNDU2XHUwNDNCXHUwNDRDXHUwNDNEXHUwNDMwIiwicG9zdGFsQ29kZSI6IjUzNjMwIn0sImNvbnRhY3RQb2ludCI6eyJuYW1lIjoiXHUwNDFBXHUwNDMwXHUwNDQwXHUwNDMwXHUwNDQxXHUwNDU0XHUwNDMyXHUwNDM4XHUwNDQ3IFx1MDQwNFx1MDQzMlx1MDQzM1x1MDQzNVx1MDQzRFx1MDQ1Nlx1MDQ0RiBcdTA0MTJcdTA0M0VcdTA0M0JcdTA0M0VcdTA0MzRcdTA0MzhcdTA0M0NcdTA0MzhcdTA0NDBcdTA0NTZcdTA0MzJcdTA0M0RcdTA0MzAiLCJlbWFpbCI6InlhLmJ1aHZwdUB1a3IubmV0IiwidGVsZXBob25lIjoiMDU2MzgyMjI3OSIsImZheE51bWJlciI6IjA1NjM4MjIyNzkifSwicmVnbmFtZSI6IjAyNTQxODIzIiwicmVnZW1haWwiOiJ5YS5idWh2cHVAdWtyLm5ldCIsInJlZ3Bhc3N3b3JkIjoiJDJhJDEwJDFldldqOUttbE92TkJpd2ZjYTVSM3UzcVVkTjR2RE1EZ3J3RUhxTHUudlFDNXZSVEYwRnF1In2ggge9MIIHuTCCB2GgAwIBAgIUM7bLe\/chuc4EAAAAyfYbABweOgAwDQYLKoYkAgEBAQEDAQEwggFQMVQwUgYDVQQKDEvQhtC90YTQvtGA0LzQsNGG0ZbQudC90L4t0LTQvtCy0ZbQtNC60L7QstC40Lkg0LTQtdC\/0LDRgNGC0LDQvNC10L3RgiDQlNCk0KExXjBcBgNVBAsMVdCj0L\/RgNCw0LLQu9GW0L3QvdGPICjRhtC10L3RgtGAKSDRgdC10YDRgtC40YTRltC60LDRhtGW0Zcg0LrQu9GO0YfRltCyINCG0JTQlCDQlNCk0KExYjBgBgNVBAMMWdCQ0LrRgNC10LTQuNGC0L7QstCw0L3QuNC5INGG0LXQvdGC0YAg0YHQtdGA0YLQuNGE0ZbQutCw0YbRltGXINC60LvRjtGH0ZbQsiDQhtCU0JQg0JTQpNChMRQwEgYDVQQFDAtVQS0zOTM4NDQ3NjELMAkGA1UEBhMCVUExETAPBgNVBAcMCNCa0LjRl9CyMB4XDTE1MTEwMzIyMDAwMFoXDTE3MTEwMzIyMDAwMFowggEmMWEwXwYDVQQKDFjQntCg0JTQldCd0JAgItCX0J3QkNCaINCf0J7QqNCQ0J3QmCIg0JLQmNCp0JUg0J\/QoNCe0KTQldCh0IbQmdCd0JUg0KPQp9CY0JvQmNCp0JUg4oSWIDc1MRYwFAYDVQQLDA3QktCf0KMg4oSWIDc1MTowOAYDVQQDDDHQtdC70LXQutGC0YDQvtC90L3QsCDQv9C10YfQsNGC0LrQsCDQktCf0KMg4oSWIDc1MRAwDgYDVQQFDAcxODMyNjQ5MQswCQYDVQQGEwJVQTEjMCEGA1UEBwwa0J7Qu9C10LrRgdCw0L3QtNGA0ZbQstC60LAxKTAnBgNVBAgMINCU0L3RltC\/0YDQvtC\/0LXRgtGA0L7QstGB0YzQutCwMIHyMIHJBgsqhiQCAQEBAQMBATCBuTB1MAcCAgEBAgEMAgEABCEQvuPbauqeH4ZXjEXBJZT\/lCOUp9c4+Rh+ZRUBcpT0zgECIQCAAAAAAAAAAAAAAAAAAAAAZ1khOvGC6YfT4XcUkH1HDQQhtg\/S2NzoqTQjxhAbypHEegB+bDALJs1VbJsOfSDvKSoABECp1utF8TxwgoDElnsjH16t9ljrpMA3KR042WvwJcpOF\/jpcg3GFbQ6KJdfC8Heo2Q4tWTqLBef0BI+bbj6xXkEAyQABCHckdowv3kz\/IRp61GOUL2vCRiu9qx3+Aw9v+mnLRDrBQCjggOgMIIDnDApBgNVHQ4EIgQgHrztTpm5jN4OlppQJAn0fipdyE0fxGHhNXMgnoSFeMUwKwYDVR0jBCQwIoAgM7bLe\/chuc7u494uYv7qO3AaS2dgvBwvzzVlFrUOvKowLwYDVR0QBCgwJqARGA8yMDE1MTEwMzIyMDAwMFqhERgPMjAxNzExMDMyMjAwMDBaMA4GA1UdDwEB\/wQEAwIGwDAXBgNVHSUBAf8EDTALBgkqhiQCAQEBAwkwGQYDVR0gAQH\/BA8wDTALBgkqhiQCAQEBAgIwDAYDVR0TAQH\/BAIwADA3BggrBgEFBQcBAwEB\/wQoMCYwCwYJKoYkAgEBAQIBMBcGBgQAjkYBAjANEwNVQUgCAw9CQAIBADCB\/QYDVR0RBIH1MIHyoIGdBgwrBgEEAYGXRgEBBAKggYwMgYk1MzYzMCwg0JTQvdGW0L\/RgNC+0L\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"}';
        // new from email
        //$data = '{"kind":"general","name":"\u0422\u041E\u0412\"\u041F\u0410\u0420\u0423\u0421-\u0420\u0415\u0413\u0406\u041E\u041D\u0418\"","identifier":{"scheme":"UA-EDR","id":"35757692","legalName":"\u0422\u041E\u0412\"\u041F\u0410\u0420\u0423\u0421-\u0420\u0415\u0413\u0406\u041E\u041D\u0418\"","url":"https://usr.minjust.gov.ua/"},"address":{"UTregionid":6,"countryName":"\u0423\u043A\u0440\u0430\u0457\u043D\u0430","locality":"\u0414\u043D\u0456\u043F\u0440\u043E\u043F\u0435\u0442\u0440\u043E\u0432\u0441\u044C\u043A","region":"\u0414\u041D\u0406\u041F\u0420\u041E\u041F\u0415\u0422\u0420\u041E\u0412\u0421\u042C\u041A\u0410 \u041E\u0411\u041B\u0410\u0421\u0422\u042C","streetAddress":"\u041B\u0430\u0431\u043E\u0440\u0430\u0442\u043E\u0440\u043D\u0430","postalCode":"04111"},"contactPoint":{"name":"\u0418\u0432\u0430\u043D\u043E\u0432 \u0418\u0432\u0430\u043D \u0418\u0432\u0430\u043D\u043E\u0432\u0438\u0447","email":"savva@parus.com.ua","telephone":"(45648) 654654","faxNumber":"(45648) 654655"},"regname":"35757692","regemail":"savva@parus.com.ua","regpassword":"$2a$10$vr48YVjuSYwuzCMkOtH.XuvODNsAqD1ge2qBwl9ekmTuvxkjCNr8C","sign":"MIIM8gYJKoZIhvcNAQcCoIIM4zCCDN8CAQExDjAMBgoqhiQCAQEBAQIBMIIEcgYJKoZIhvcNAQcBoIIEYwSCBF97ImtpbmQiOiJnZW5lcmFsIiwibmFtZSI6Ilx1MDQyMlx1MDQxRVx1MDQxMlwiXHUwNDFGXHUwNDEwXHUwNDIwXHUwNDIzXHUwNDIxLVx1MDQyMFx1MDQxNVx1MDQxM1x1MDQwNlx1MDQxRVx1MDQxRFx1MDQxOFwiIiwiaWRlbnRpZmllciI6eyJzY2hlbWUiOiJVQS1FRFIiLCJpZCI6IjM1NzU3NjkyIiwibGVnYWxOYW1lIjoiXHUwNDIyXHUwNDFFXHUwNDEyXCJcdTA0MUZcdTA0MTBcdTA0MjBcdTA0MjNcdTA0MjEtXHUwNDIwXHUwNDE1XHUwNDEzXHUwNDA2XHUwNDFFXHUwNDFEXHUwNDE4XCIiLCJ1cmwiOiJodHRwczovL3Vzci5taW5qdXN0Lmdvdi51YS8ifSwiYWRkcmVzcyI6eyJVVHJlZ2lvbmlkIjo2LCJjb3VudHJ5TmFtZSI6Ilx1MDQyM1x1MDQzQVx1MDQ0MFx1MDQzMFx1MDQ1N1x1MDQzRFx1MDQzMCIsImxvY2FsaXR5IjoiXHUwNDE0XHUwNDNEXHUwNDU2XHUwNDNGXHUwNDQwXHUwNDNFXHUwNDNGXHUwNDM1XHUwNDQyXHUwNDQwXHUwNDNFXHUwNDMyXHUwNDQxXHUwNDRDXHUwNDNBIiwicmVnaW9uIjoiXHUwNDE0XHUwNDFEXHUwNDA2XHUwNDFGXHUwNDIwXHUwNDFFXHUwNDFGXHUwNDE1XHUwNDIyXHUwNDIwXHUwNDFFXHUwNDEyXHUwNDIxXHUwNDJDXHUwNDFBXHUwNDEwIFx1MDQxRVx1MDQxMVx1MDQxQlx1MDQxMFx1MDQyMVx1MDQyMlx1MDQyQyIsInN0cmVldEFkZHJlc3MiOiJcdTA0MUJcdTA0MzBcdTA0MzFcdTA0M0VcdTA0NDBcdTA0MzBcdTA0NDJcdTA0M0VcdTA0NDBcdTA0M0RcdTA0MzAiLCJwb3N0YWxDb2RlIjoiMDQxMTEifSwiY29udGFjdFBvaW50Ijp7Im5hbWUiOiJcdTA0MThcdTA0MzJcdTA0MzBcdTA0M0RcdTA0M0VcdTA0MzIgXHUwNDE4XHUw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"}';
        // new shit 547946 org id


        //$data = '{"kind":"general","name":"\u041a\u043e\u043c\u0443\u043d\u0430\u043b\u044c\u043d\u0438\u0439 \u0437\u0430\u043a\u043b\u0430\u0434 \"\u041c\u043e\u0433\u0438\u043b\u0456\u0432\u0441\u044c\u043a\u0438\u0439 \u0433\u0435\u0440\u0456\u0430\u0442\u0440\u0438\u0447\u043d\u0438\u0439 \u043f\u0430\u043d\u0441\u0456\u043e\u043d\u0430\u0442\" \u0414\u043d\u0456\u043f\u0440\u043e\u043f\u0435\u0442\u0440\u043e\u0432\u0441\u044c\u043a\u043e\u0457 \u043e\u0431\u043b\u0430\u0441\u043d\u043e\u0457 \u0440\u0430\u0434\u0438\"","identifier":{"scheme":"","id":"21911036","legalName":"\u041a\u043e\u043c\u0443\u043d\u0430\u043b\u044c\u043d\u0438\u0439 \u0437\u0430\u043a\u043b\u0430\u0434 \"\u041c\u043e\u0433\u0438\u043b\u0456\u0432\u0441\u044c\u043a\u0438\u0439 \u0433\u0435\u0440\u0456\u0430\u0442\u0440\u0438\u0447\u043d\u0438\u0439 \u043f\u0430\u043d\u0441\u0456\u043e\u043d\u0430\u0442\" \u0414\u043d\u0456\u043f\u0440\u043e\u043f\u0435\u0442\u0440\u043e\u0432\u0441\u044c\u043a\u043e\u0457 \u043e\u0431\u043b\u0430\u0441\u043d\u043e\u0457 \u0440\u0430\u0434\u0438\"","url":""},"address":{"UTregionid":6,"countryName":"\u0423\u043a\u0440\u0430\u0457\u043d\u0430","locality":"\u041c\u043e\u0433\u0438\u043b\u0456\u0432","region":"\u0414\u041d\u0406\u041f\u0420\u041e\u041f\u0415\u0422\u0420\u041e\u0412\u0421\u042c\u041a\u0410 \u041e\u0411\u041b\u0410\u0421\u0422\u042c","streetAddress":"\u0411\u0435\u0440\u0435\u0433\u043e\u0432\u0430","postalCode":"05104"},"contactPoint":{"name":"\u0428\u043c\u0443\u043b\u044c \u0406\u0440\u0438\u043d\u0430 \u041e\u043b\u0435\u043a\u0441\u0456\u0457\u0432\u043d\u0430","email":"mogpansion@ukrpost.ua","telephone":"8 (290) 6-31-25","faxNumber":"8 (290) 6-31-25"},"regname":"21911036","regemail":"mogpansion@ukrpost.ua","regpassword":"$2a$10$Y6YJhgGRARTkxYwftlqjHO4uBzRwjP9z.4lye09Pvrri4jrdT2HRG","sign":"MIIXNwYJKoZIhvcNAQcCoIIXKDCCFyQCAQExDjAMBgoqhiQCAQEBAQIBMIIHMwYJKoZIhvcNAQcBoIIHJASCByB7ImtpbmQiOiJnZW5lcmFsIiwibmFtZSI6Ilx1MDQxQVx1MDQzRVx1MDQzQ1x1MDQ0M1x1MDQzRFx1MDQzMFx1MDQzQlx1MDQ0Q1x1MDQzRFx1MDQzOFx1MDQzOSBcdTA0MzdcdTA0MzBcdTA0M0FcdTA0M0JcdTA0MzBcdTA0MzQgXCJcdTA0MUNcdTA0M0VcdTA0MzNcdTA0MzhcdTA0M0JcdTA0NTZcdTA0MzJcdTA0NDFcdTA0NENcdTA0M0FcdTA0MzhcdTA0MzkgXHUwNDMzXHUwNDM1XHUwNDQwXHUwNDU2XHUwNDMwXHUwNDQyXHUwNDQwXHUwNDM4XHUwNDQ3XHUwNDNEXHUwNDM4XHUwNDM5IFx1MDQzRlx1MDQzMFx1MDQzRFx1MDQ0MVx1MDQ1Nlx1MDQzRVx1MDQzRFx1MDQzMFx1MDQ0MlwiIFx1MDQxNFx1MDQzRFx1MDQ1Nlx1MDQzRlx1MDQ0MFx1MDQzRVx1MDQzRlx1MDQzNVx1MDQ0Mlx1MDQ0MFx1MDQzRVx1MDQzMlx1MDQ0MVx1MDQ0Q1x1MDQzQVx1MDQzRVx1MDQ1NyBcdTA0M0VcdTA0MzFcdTA0M0JcdTA0MzBcdTA0NDFcdTA0M0RcdTA0M0VcdTA0NTcgXHUwNDQwXHUwNDMwXHUwNDM0XHUwNDM4XCIiLCJpZGVudGlmaWVyIjp7InNjaGVtZSI6IiIsImlkIjoiMjE5MTEwMzYiLCJsZWdhbE5hbWUiOiJcdTA0MUFcdTA0M0VcdTA0M0NcdTA0NDNcdTA0M0RcdTA0MzBcdTA0M0JcdTA0NENcdTA0M0RcdTA0MzhcdTA0MzkgXHUwNDM3XHUwNDMwXHUwNDNBXHUwNDNCXHUwNDMwXHUwNDM0IFwiXHUwNDFDXHUwNDNFXHUwNDMzXHUwNDM4XHUwNDNCXHUwNDU2XHUwNDMyXHUwNDQxXHUwNDRDXHUwNDNBXHUwNDM4XHUwNDM5IFx1MDQzM1x1MDQzNVx1MDQ0MFx1MDQ1Nlx1MDQzMFx1MDQ0Mlx1MDQ0MFx1MDQzOFx1MDQ0N1x1MDQzRFx1MDQzOFx1MDQzOSBcdTA0M0ZcdTA0MzBcdTA0M0RcdTA0NDFcdTA0NTZcdTA0M0VcdTA0M0RcdTA0MzBcdTA0NDJcIiBcdTA0MTRcdTA0M0RcdTA0NTZcdTA0M0ZcdTA0NDBcdTA0M0VcdTA0M0ZcdTA0MzVcdTA0NDJcdTA0NDBcdTA0M0VcdTA0MzJcdTA0NDFcdTA0NENcdTA0M0FcdTA0M0VcdTA0NTcgXHUwNDNFXHUwNDMxXHUwNDNCXHUwNDMwXHUwNDQxXHUwNDNEXHUwNDNFXHUwNDU3IFx1MDQ0MFx1MDQzMFx1MDQzNFx1MDQzOFwiIiwidXJsIjoiIn0sImFkZHJlc3MiOnsiVVRyZWdpb25pZCI6NiwiY291bnRyeU5hbWUiOiJcdTA0MjNcdTA0M0FcdTA0NDBcdTA0MzBcdTA0NTdcdTA0M0RcdTA0MzAiLCJsb2NhbGl0eSI6Ilx1MDQxQ1x1MDQzRVx1MDQzM1x1MDQzOFx1MDQzQlx1MDQ1Nlx1MDQzMiIsInJlZ2lvbiI6Ilx1MDQxNFx1MDQxRFx1MDQwNlx1MDQxRlx1MDQyMFx1MDQxRVx1MDQxRlx1MDQxNVx1MDQyMlx1MDQyMFx1MDQxRVx1MDQxMlx1MDQyMVx1MDQyQ1x1MDQxQVx1MDQxMCBcdTA0MUVcdTA0MTFcdTA0MUJcdTA0MTBcdTA0MjFcdTA0MjJcdTA0MkMiLCJzdHJlZXRBZGRyZXNzIjoiXHUwNDExXHUwNDM1XHUwNDQwXHUwNDM1XHUwNDMzXHUwNDNFXHUwNDMyXHUwNDMwIiwicG9zdGFsQ29kZSI6IjA1MTA0In0sImNvbnRhY3RQb2ludCI6eyJuYW1lIjoiXHUwNDI4XHUwNDNDXHUwNDQzXHUwNDNCXHUwNDRDIFx1MDQwNlx1MDQ0MFx1MDQzOFx1MDQzRFx1MDQzMCBcdTA0MUVcdTA0M0JcdTA0MzVcdTA0M0FcdTA0NDFcdTA0NTZcdTA0NTdcdTA0MzJcdTA0M0RcdTA0MzAiLCJlbWFpbCI6Im1vZ3BhbnNpb25AdWtycG9zdC51YSIsInRlbGVwaG9uZSI6IjggKDI5MCkgNi0zMS0yNSIsImZheE51bWJlciI6IjggKDI5MCkgNi0zMS0yNSJ9LCJyZWduYW1lIjoiMjE5MTEwMzYiLCJyZWdlbWFpbCI6Im1vZ3BhbnNpb25AdWtycG9zdC51YSIsInJlZ3Bhc3N3b3JkIjoiJDJhJDEwJFk2WUpoZ0dSQVJUa3hZd2Z0bHFqSE80dUJ6UndqUDl6LjRseWUwOVB2cnJpNGpyZFQySFJHIn2ggge\/MIIHuzCCB2OgAwIBAgIUM7bLe\/chuc4EAAAAS68dAKt4OwAwDQYLKoYkAgEBAQEDAQEwggFQMVQwUgYDVQQKDEvQhtC90YTQvtGA0LzQsNGG0ZbQudC90L4t0LTQvtCy0ZbQtNC60L7QstC40Lkg0LTQtdC\/0LDRgNGC0LDQvNC10L3RgiDQlNCk0KExXjBcBgNVBAsMVdCj0L\/RgNCw0LLQu9GW0L3QvdGPICjRhtC10L3RgtGAKSDRgdC10YDRgtC40YTRltC60LDRhtGW0Zcg0LrQu9GO0YfRltCyINCG0JTQlCDQlNCk0KExYjBgBgNVBAMMWdCQ0LrRgNC10LTQuNGC0L7QstCw0L3QuNC5INGG0LXQvdGC0YAg0YHQtdGA0YLQuNGE0ZbQutCw0YbRltGXINC60LvRjtGH0ZbQsiDQhtCU0JQg0JTQpNChMRQwEgYDVQQFDAtVQS0zOTM4NDQ3NjELMAkGA1UEBhMCVUExETAPBgNVBAcMCNCa0LjRl9CyMB4XDTE1MTIwMzIyMDAwMFoXDTE3MTIwMzIyMDAwMFowggEsMTUwMwYDVQQKDCzQmtCXICLQnNCe0JPQmNCb0IbQktCh0KzQmtCY0Jkg0JPQnyIg0JTQntCgIjE1MDMGA1UECwws0JrQlyAi0JzQntCT0JjQm9CG0JLQodCs0JrQmNCZINCT0J8iINCU0J7QoCIxWTBXBgNVBAMMUNC10LvQtdC60YLRgNC+0L3QvdCwINC\/0LXRh9Cw0YLQutCwINCa0JcgItCc0J7Qk9CY0JvQhtCS0KHQrNCa0JjQmSDQk9CfIiDQlNCe0KAiMRAwDgYDVQQFDAcxOTQ1NDE5MQswCQYDVQQGEwJVQTEXMBUGA1UEBwwO0JzQvtCz0LjQu9GW0LIxKTAnBgNVBAgMINCU0L3RltC\/0YDQvtC\/0LXRgtGA0L7QstGB0YzQutCwMIHyMIHJBgsqhiQCAQEBAQMBATCBuTB1MAcCAgEBAgEMAgEABCEQvuPbauqeH4ZXjEXBJZT\/lCOUp9c4+Rh+ZRUBcpT0zgECIQCAAAAAAAAAAAAAAAAAAAAAZ1khOvGC6YfT4XcUkH1HDQQhtg\/S2NzoqTQjxhAbypHEegB+bDALJs1VbJsOfSDvKSoABECp1utF8TxwgoDElnsjH16t9ljrpMA3KR042WvwJcpOF\/jpcg3GFbQ6KJdfC8Heo2Q4tWTqLBef0BI+bbj6xXkEAyQABCErD94FHORCMuuVGiywxXRpO5f4FIk+9+R2tGoFKQ6tTgCjggOcMIIDmDApBgNVHQ4EIgQg1bicLRISm9MgASW3lPGQ9QR+22Neg8su25KNbHRY2V4wKwYDVR0jBCQwIoAgM7bLe\/chuc7u494uYv7qO3AaS2dgvBwvzzVlFrUOvKowLwYDVR0QBCgwJqARGA8yMDE1MTIwMzIyMDAwMFqhERgPMjAxNzEyMDMyMjAwMDBaMA4GA1UdDwEB\/wQEAwIGwDAXBgNVHSUBAf8EDTALBgkqhiQCAQEBAwkwGQYDVR0gAQH\/BA8wDTALBgkqhiQCAQEBAgIwDAYDVR0TAQH\/BAIwADA3BggrBgEFBQcBAwEB\/wQoMCYwCwYJKoYkAgEBAQIBMBcGBgQAjkYBAjANEwNVQUgCAw9CQAIBADCB+QYDVR0RBIHxMIHuoIGYBgwrBgEEAYGXRgEBBAKggYcMgYQ1MTA0MCwg0JTQvdGW0L\/RgNC+0L\/QtdGC0YDQvtCy0YHRjNC60LAg0L7QsdC7Liwg0KbQsNGA0LjRh9Cw0L3RgdGM0LrQuNC5INGALdC9LCDRgS4g0JzQvtCz0LjQu9GW0LIsINCy0YPQuy4g0JHQtdGA0LXQs9C+0LLQsCwgNDIg0JCgHwYMKwYBBAGBl0YBAQQBoA8MDSgwNTYpIDkwNjMxNzWBFm1vZ3BhbnNpb25AdWtycG9zdC4gdWGgGAYKKwYBBAGCNxQCA6AKDAjQlTAxLTI1NDBIBgNVHR8EQTA\/MD2gO6A5hjdodHRwOi8vYWNza2lkZC5nb3YudWEvZG93bmxvYWQvY3Jscy9BQ1NLSUREREZTLUZ1bGwuY3JsMEkGA1UdLgRCMEAwPqA8oDqGOGh0dHA6Ly9hY3NraWRkLmdvdi51YS9kb3dubG9hZC9jcmxzL0FDU0tJRERERlMtRGVsdGEuY3JsMIGIBggrBgEFBQcBAQR8MHowMAYIKwYBBQUHMAGGJGh0dHA6Ly9hY3NraWRkLmdvdi51YS9zZXJ2aWNlcy9vY3NwLzBGBggrBgEFBQcwAoY6aHR0cDovL2Fjc2tpZGQuZ292LnVhL2Rvd25sb2FkL2NlcnRpZmljYXRlcy9hbGxhY3NraWRkLnA3YjA\/BggrBgEFBQcBCwQzMDEwLwYIKwYBBQUHMAOGI2h0dHA6Ly9hY3NraWRkLmdvdi51YS9zZXJ2aWNlcy90c3AvMCUGA1UdCQQeMBwwGgYMKoYkAgEBAQsBBAIBMQoTCDIxOTExMDM2MA0GCyqGJAIBAQEBAwEBA0MABEA1F544mh7gdjOkOp3AEMiU6wBECIm7nI2GRS13ZyFdDuP445rmTXVtHy4dPHBRdOUw5bxDvfbddf+o8Cj6RKBsMYIIEzCCCA8CAQEwggFqMIIBUDFUMFIGA1UECgxL0IbQvdGE0L7RgNC80LDRhtGW0LnQvdC+LdC00L7QstGW0LTQutC+0LLQuNC5INC00LXQv9Cw0YDRgtCw0LzQtdC90YIg0JTQpNChMV4wXAYDVQQLDFXQo9C\/0YDQsNCy0LvRltC90L3RjyAo0YbQtdC90YLRgCkg0YHQtdGA0YLQuNGE0ZbQutCw0YbRltGXINC60LvRjtGH0ZbQsiDQhtCU0JQg0JTQpNChMWIwYAYDVQQDDFnQkNC60YDQtdC00LjRgtC+0LLQsNC90LjQuSDRhtC10L3RgtGAINGB0LXRgNGC0LjRhNGW0LrQsNGG0ZbRlyDQutC70Y7Rh9GW0LIg0IbQlNCUINCU0KTQoTEUMBIGA1UEBQwLVUEtMzkzODQ0NzYxCzAJBgNVBAYTAlVBMREwDwYDVQQHDAjQmtC40ZfQsgIUM7bLe\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"}';


       // dd(json_encode($json));


        /**
         * works
         */
        //dd($result);

    }

}
