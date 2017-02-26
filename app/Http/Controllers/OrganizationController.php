<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\CreateOrganizationRequest;
use App\Model\Contacts;
use App\Model\Country;
use App\Model\Identifier;
use App\Model\IdentifierOrganization;
use App\Model\Languages;
use App\Model\Notification;
use App\Model\Organization;
use App\Model\TendersRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Validator;

class OrganizationController extends Controller {

    protected function _validateContact($data)
    {

        $rules = [
            'contact_name_en'=> 'required',
            'contact_name'   => 'required',
            'contact_email'  => 'required|email',
            'contact_phone'  => 'required|ukrainian_phone' //checkout regexp in AppServiceProvider file
        ];

        $messages = [
            'contact_name_en.required' => 'Поле "Контактна особа англійською мовою" необхідно заповнити',
            'contact_name.required' => 'Поле "Контактна особа" необхідно заповнити',
            'contact_email.required' => 'Поле "Email" необхідно заповнити',
            'contact_email.email' => 'Поле "Email" повинно бути у вказанному форматі',
            'contact_phone.required' => 'Поле "Телефон" необхідно заповнити',
//            'contact_phone.regex' => 'Поле "Телефон" повинно бути у вказанному форматі',
            'contact_phone.ukrainian_phone' => 'Поле "Телефон" повинно бути у вказанному форматі'
        ];

        foreach ($data as $contact) {
            $validate = Validator::make($contact, $rules, $messages);
            if ($validate->fails())
                return $validate;
        }

        return true;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $organizations = Auth::user()->organization()->get();

        return view('pages.organization.lists', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $organization = Auth::user()->organization;

        if($organization) {
            return redirect()->route('organization.edit');
        }

        $user = Auth::user();

        $regions   = TendersRegions::orderBy('region_name')->active()->lists('region_ua', 'id');
        $languages = Languages::active()->lists('language_name', 'language_code');
        $countries = Country::active()->lists('country_name_ua', 'country_iso');
        

        $schemes = groupByValue(Identifier::active()->lists('country_iso', 'scheme'));

        return view('pages.organization.create', compact('regions', 'user', 'languages', 'countries', 'schemes'));
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

        $data                                = $request->all();
        $contact                             = $data['contact'];
        $primaryContact                      = $data['primary_contact'];
        $contact[$primaryContact]['primary'] = 1;

        $validator = $this->_validateContact($contact);
        if ($validator !== true && $validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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
            if ($region) {
                $data["region_name"] = $region->region_ua;
            }
            $scheme = Identifier::where('scheme', 'UA-EDR')->first();
        } else {
            //$scheme = $country->identifiers()->first(); конфликтная строка
            $data["region_id"]=0;
            $scheme = $country->identifiers()->where('scheme', 'like', $data['country_scheme'])->first();
        }
        $data["contact_name"]           = $data['contact'][$primaryContact]['contact_name'];
        $data["contact_name_en"]        = $data['contact'][$primaryContact]['contact_name_en'];
        $data["contact_phone"]          = $data['contact'][$primaryContact]['contact_phone'];
        $data["contact_email"]          = $data['contact'][$primaryContact]['contact_email'];
        $data["contact_available_lang"] = $data['contact'][$primaryContact]['contact_available_lang'];

        unset($data['contact']);
        unset($data['primary_contact']);


        $organization = new Organization($data);

        $organization->ina_hash = hashFromArray([
            $data["organization_identifier"],
            $data['name'],
            $organization->getAddress(),
        ]);

        $organization->save();

        foreach ($contact as $key => $cont) {
            if($key == $primaryContact){
                $cont['primary'] = '1';
            } else {
                $cont['primary'] = '0';
            }
            $cont['organization_id'] = $organization->id;

            $contacts = new Contacts($cont);
            $contacts->save();
        }

        $organizationIdentifiers = new IdentifierOrganization([
            'organisation_id' => $organization->id,
            'identifier_id'   => (isset($scheme)) ? $scheme->id : 0,
            'identifier'      => $data["organization_identifier"],
        ]);
        $organizationIdentifiers->save();

        Auth::user()->organization()->save($organization);
        if ($organization->type == 'customer') {
            Session::flash('flash_modal', trans('messages.agreement.customer'));
        } else {
            \Session::flash('flash_modal', "                        <h2>Шановний Користувач електронного майданчика \"Zakupki UA\",</h2>
                <p>для використання електронного майданчика \"Zakupki UA\" з метою участі у якості учасника у закупівлях у відповідності до законодавства у сфері публічних закупівель, кожен Користувач, крім реєстрації/авторизації на електронному майданчику \"Zakupki UA\", має:
<ul><li>прийняти (акцептувати) Пропозицію ТОВАРИСТВА З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ \"ЗАКУПІВЛІ ЮА\" укласти Договір про надання послуг (приєднатися до Договору) шляхом здійснення оплати з власного поточного (розрахункового) рахунку за наданим Оператором рахунком, відповідно до умов Договору;</li>
<li>пройти ідентифікацію та отримати від Оператора доступ до електронної системи закупівель.</li>
</ul></p>

                <p>З повагою,<br>
                Служба підтримки Zakupki UA<br>
                support@zakupki.com.ua</p>");
        }


        return redirect()->route('home');

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
    public function edit() {
        $organization = Auth::user()->organization;
        if(!empty($organization)) {
            if ($organization->contacts()->count() != 0) {
                $conts = $organization->contacts;
            } else {
                $contact = [
                    "organization_id" => $organization->id,
                    "primary" => 1,
                    "contact_name" => $organization->contact_name,
                    "contact_name_en" => $organization->contact_name_en,
                    "contact_email" => $organization->contact_email,
                    "contact_phone" => $organization->contact_phone,
                    "contact_fax" => $organization->contact_fax,
                    "contact_url" => $organization->contact_url,
                    "contact_available_lang" => $organization->contact_available_lang,];
                $contacs = new Contacts($contact);
                $contacs->save();
                $conts = $organization->contacts;
            }
        }
        else{

            return redirect()->route('organization.create');
        }
        $regions = TendersRegions::orderBy('region_name')->active()->lists('region_ua', 'id');
        $languages = Languages::active()->lists('language_name', 'language_code');
        $countries = Country::active()->lists('country_name_ua', 'country_iso');

        $schemes = Identifier::all()->lists('country_iso', 'scheme');
        $schemes = groupByValue($schemes);

        return view('pages.organization.edit', compact('organization', 'regions', 'conts', 'languages', 'countries', 'schemes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int     $id
     *
     * @return Response
     */
    public function update(CreateOrganizationRequest $request)
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

        $validator = $this->_validateContact($contact);
        if ($validator !== true && $validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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
            $scheme = $country->identifiers()->first();
        }

        $contact[$primaryContact]['primary'] = 1;
        unset($data['contact']);
        foreach ($contact as $key => $cont) {
            $cont['organization_id'] = Auth::user()->organization->id;
            if (isset($cont['primary']) && $cont['primary'] == 1) {
                $data["contact_name"] = $cont['contact_name'];
                $data["contact_name_en"] = $cont['contact_name_en'];
                $data["contact_phone"] = $cont['contact_phone'];
                $data["contact_email"] = $cont['contact_email'];
                if (isset( $cont['contact_url'])){
                    $data["contact_url"] = $cont['contact_url'];
                }
                $data["contact_available_lang"] = $cont['contact_available_lang'];
                $contactModel = Contacts::find($key);

                if ($contactModel != null) {
                    $contactModel->update($cont);
                } else {
                    $cm = new Contacts($cont);
                    $cm->save();
                }
            }else {
                $contactModel = Contacts::find($key);
                if ($contactModel != null) {
                    $contactModel->contact_name = $cont['contact_name'];
                    $contactModel->contact_name_en = $cont['contact_name_en'];
                    $contactModel->contact_phone = $cont['contact_phone'];
                    $contactModel->contact_email = $cont['contact_email'];
                    if(isset($cont['contact_url'])) {
                        $contactModel->contact_url = $cont['contact_url'];
                    }
                    $contactModel->contact_available_lang = $cont['contact_available_lang'];
                    $contactModel->primary = "0";
                    $contactModel->save();
                } else {
                    $cm = new Contacts($cont);
                    $cm->save();
                }
            }
        }

        $organization = Auth::user()->organization;
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

        $ina_hash = hashFromArray([
            $data['organization_identifier'],
            $data['name'],
            $organization->getAddress(),
        ]);

        $organization->update([
            'ina_hash' => $ina_hash,
        ]);

        Mail::queue('emails.admin.changeOrganizationData', compact('organization'), function ($message) {
            $message->to('support@zakupki.com.ua', 'support@zakupki.com.ua')->subject('Зміна даних організації');
            $message->to('spasibova@zakupki.com.ua', 'support@zakupki.com.ua')->subject('Зміна даних організації');
            $message->to('manager@zakupki.com.ua', 'support@zakupki.com.ua')->subject('Зміна даних організації');
        });

        session()->flash('flash_message', 'Дані успішно оновлені.');

        return redirect()->route('organization.edit');
    }

    public function contacts(){
        $id   = $_POST['id'];
        $resp = Contacts::find($id);
        $data = [
            'contact_id'             => $resp->id,
            'contact_name'           => $resp->contact_name,
            'contact_name_en'        => $resp->contact_name_en,
            'contact_phone'          => $resp->contact_phone,
            'contact_email'          => $resp->contact_email,
            'contact_url'            => $resp->contact_url,
            'contact_available_lang' => $resp->contact_available_lang,
        ];

        return response()->json($data);
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

    public function mode(Request $request)
    {
        $organization = Auth::user()->organization;
        if($organization->confirmed) {
            $organization->mode = $request->get('m');
            $organization->save();

            Session::flash('flash_message', 'Режим успішно змінено');
        } else {
            Session::flash('flash_error', 'Реальний режим роботи буде доступний після перевірки адміністратором Ваших реєстраційних даних');
        }

        return redirect()->back();
    }

    public function switchType()
    {
        $org = Auth::user()->organization;
        if($org->type == 'supplier') {
            $org->type = 'customer';
            $org->kind_id = 2;
            $org->save();
            return redirect()->route('tender.index');
        }

        if ($org->type == 'customer') {
            $org->type = 'supplier';
            $org->kind_id = 0;
            $org->save();
            return redirect()->route('tender.index');
        }
    }
}
