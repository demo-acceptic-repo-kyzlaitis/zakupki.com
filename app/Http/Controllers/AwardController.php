<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Events\AwardSaveEvent;
use App\Events\AwardDocUploadEvent;
use App\Model\Award;
use App\Model\AwardDocuments;
use App\Model\Country;
use App\Model\Currencies;
use App\Model\Identifier;
use App\Model\IdentifierOrganization;
use App\Model\Organization;
use App\Model\Tender;
use App\Model\TendersRegions;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Validator;

class AwardController extends Controller
{

    protected function _uploadDocs($award, $files, $path, $isNew = false) {
        if (is_array($files)) {
            foreach ($files as $index => $file) {
                if ($file) {
                    Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                    $params = [
                        'path'        => $path . $file->getClientOriginalName(),
                    ];

                    if ($isNew) {
                        $oldDoc = AwardDocuments::find($index);
                        $params['type_id'] = $oldDoc->type_id;
                        $params['orig_id'] = $oldDoc->orig_id;
                        $params['url'] = '';
                        $params['title'] = '';
                        $oldDoc->update($params);
                    } else {
                        $newDoc = new AwardDocuments($params);
                        $award->documents()->save($newDoc);
                    }
                }
            }
        }
    }

    public function index()
    {
        $tenders = \Auth::user()->organization->tenders()->where('status', 'active.qualification')->paginate(20);

        return view('pages.award.user_list', ['tenders' => $tenders]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function tender($tenderId)
    {
        $tender = \Auth::user()->organization->tenders()->findOrFail($tenderId);

        if($tender->hasAnyAcceptedComplaints()) {
            Session::flash('flash_error', 'Органом оскарження було прийнято скаргу до розгляду. У цей період
                    замовнику забороняється вчиняти будь-які дії та приймати будь-які рішення щодо закупівлі,у тому
                    числі, укладення договору про закупівлю, крім дій, спрямованих на усунення порушень, зазначених у скарзі');
        }
        //Session::flash('flash_error','test');

        return view('pages.award.list', compact('tender'));
    }

    protected function _validate(Request $request, $tender = null) {

        if($request->has('organization')) {
            $dataAll = $request->all();

            $data  = $request->get('organization');
            $rules = [
                'name'           => 'required',
                'identifier'     => 'required|regex:/^(\d{8})(\d{2})?$/',
                'region_id'      => 'required',
                'locality'       => 'required',
                'street_address' => 'required',
                'contact_email'  => 'required|email',
                'contact_name'   => 'required',
                'postal_code'    => 'required|numeric|digits_between:5,5',
                'contact_phone'  => 'required|ukrainianPhone',
            ];

            if($tender && $tender->type_id != 4) {
                $data['documentsCount']  = $this->_existDocuments($dataAll);
                $rules['documentsCount'] = 'in:1';
            }

            if($data['country_id']) {
                $rules['identifier']    = 'required';
                $rules['postal_code']   = 'required|numeric';
                $rules['contact_phone'] = 'required';
            }

            if($tender) {
                if($request->get('amount') <= $tender->amount) {
                    $data['less'] = 1;
                } else {
                    $data['less'] = 0;
                }
            }

            if($tender) {
                $rules['less'] = 'in:1';
            }

            $messages = [
                'name.required'                 => 'Поле "Назва організації" необхідно заповнити',
                'contact_phone.ukrainian_phone' => 'Поле "Телефон" повинно містити телефон у форматі +380935017175',
                'contact_phone.required'        => 'Поле "Телефон" необхідно заповнити',
                'postal_code.numeric'           => 'Поле "Поштовий індекс" повинно бути числом і містити 5 цифр',
                'postal_code.required'          => 'Поле "Поштовий індекс" необхідно заповнити',
                'contact_email.required'        => 'Поле "Email" необхідно заповнити',
                'contact_email.email'           => 'Поле "Email" повинно мати формат "example@domain.com" ',
                'contact_name.required'         => 'Поле "Контактна особа" необхідно заповнити',
                'region_id.required'            => 'Поле "Регіон" необхідно заповнити',
                'identifier.required'           => 'Поле "Код за ЄДРПОУ" або "Реєстраційний номер облікової картки платника податків" необхідно заповнити',
                'identifier.regex'              => 'Поле "Код за ЄДРПОУ" або "Реєстраційний номер облікової картки платника податків" повинно бути числом та містити 8 ціфр або 10',
                'documentsCount.in'             => 'Для створення закупівлі додайте документи та натисніть Створити',
            ];

            if($data['country_id']) {
                $messages['identifier.required'] = 'Поле "Ідентифікатор" необхідно заповнити';
            }
            if($tender) {
                $messages['less.in'] = 'Сума пропозиції не повинна перевищувати очікувану вартість';
            }

        } else {
            $data = $rules = $messages = [];
        }

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id) {
        if(Auth::user()->super_user) {
            $tender = Tender::find($id);
        } else {
            $tender = Auth::user()->organization->tenders()->findOrFail($id);
        }

        if($tender->procedureType->procurement_method == 'limited') {
            if($tender->awards()->where('status', 'pending')->count()) {
                Session::flash('flash_modal', 'Для того, щоб додати нового учасника, потрібно спочатку визначити роль попереднього. <br><br>Зверніть увагу, що переможець додається останнім.');

                return redirect()->back();
            }
        }

        $countries  = Country::active()->orderBy('country_name_ua')->lists('country_name_ua', 'country_iso');
        $regions    = TendersRegions::orderBy('id')->active()->lists('region_ua', 'id');
        $currencies = Currencies::orderBy('id')->lists('currency_code', 'id');
        $schemes    = groupByValue(Identifier::active()->lists('country_iso', 'scheme'));

        return view('pages.award.create', ['tender'     => $tender,
                                           'regions'    => $regions,
										   'region_id'  => 26,
                                           'currencies' => $currencies,
                                           'countries'  => $countries,
                                           'country_id' => 'UA',
                                           'schemes'    => $schemes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request) {

        $data = $request->all();
        $tender = Tender::find($data['tender_id']);

        $validator = $this->_validate($request, $tender);

        if($validator->fails()) {
            if($request->ajax()) {
                return response()->json([$validator->getMessageBag()->all()], 400);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        $organizationData = $request->get('organization');
        $identifierID = $organizationData['identifier'];

        $country = Country::where('country_iso', $organizationData['country_id'])->first();
        if (!$country)
            $country = Country::findOrFail(228); //228 - id Украины
        $organizationData["country_id"] = $country->id;
        if ($organizationData["country_id"] == 228) {
            $tenderRegion = TendersRegions::find($organizationData['region_id']);
            $organizationData['region_name'] = ($tenderRegion) ? $tenderRegion->region_ua : '';
        }
        $organization = new Organization($organizationData);
        $organization->save();

        $scheme = Identifier::where('country_iso', $country->country_iso)->first();
        $organizationIdentifiers = new IdentifierOrganization([
            'organisation_id' => $organization->id,
            'identifier_id' => (isset($scheme)) ? $scheme->id : 0,
            'identifier' => $identifierID
        ]);
        $organizationIdentifiers->save();
//        }

        $data['organization_id'] = $organization->id;
        $data['tender_id'] = $request->get('tender_id');
        $data['status'] = 'pending';


        if ($tender){
            $data['tax_included'] = $tender->tax_included;
        }

        $award = new Award($data);
        $award->save();

        $this->_uploadDocs($award, $request->file('award')[array_keys($request->all()['award'])[0]]['files'], "/awards/{$award->id}/");
        Event::fire(new AwardSaveEvent($award));
        Session::flash('flash_message', 'Дані успішно оновлені');

        if ($award->tender->procedureType->procurement_method == 'limited' && $award->tender->procedureType->procurement_methoda_type != 'reporting') {
            return redirect()->route('award.list', $award->tender->id);
        }

        return redirect()->route('award.edit', $award->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
    	$award = Award::find($id);
        $tender = $award->tender;

        //костыль мотыль
        $this->getMissingInfo($tender, $award);

        return view('pages.award.detail', compact('award', 'tender'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $award = Award::with('organization')->findOrFail($id);
        if(!Auth::user()->super_user && ($award->tender->organization && $award->tender->organization->id != \Auth::user()->organization->id)) {
            abort(403);
        }
        $country_id = Country::find($award->organization->country_id)->country_iso;


        $countries  = Country::active()->orderBy('country_name_ua')->lists('country_name_ua', 'country_iso');

        $regions    = TendersRegions::orderBy('id')->active()->lists('region_ua', 'id');
        $region_id = $award->organization->region_id;
        $currencies = Currencies::orderBy('id')->lists('currency_code', 'id');
        $schemes    = groupByValue(Identifier::active()->lists('country_iso', 'scheme'));

//        $award->organization->



        return view('pages.award.edit', ['award'      => $award,
                                         'tender'     => $award->tender,
                                         'regions'    => $regions,
        								 'region_id' => $region_id,
                                         'currencies' => $currencies,
                                         'countries'  => $countries,
                                         'country_id' => $country_id,
                                         'schemes'    => $schemes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $tender = Tender::find($request->get('tender_id'));

        $validator = $this->_validate($request, $tender);

        if($validator->fails()) {
            if($request->ajax()) {
                return response()->json([$validator->getMessageBag()->all()], 400);
            } else {
                return redirect()->route('award.edit', [$id])
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $award = Award::findOrFail($id);
        if (!Auth::user()->super_user && ($award->tender->organization->id != \Auth::user()->organization->id)) {
            abort(403);
        }
        $data = $request->all();
        $organizationData = $request->get('organization');
        if ($organizationData) {
            $identifierID = $organizationData['identifier'];
            $organization = Organization::getByIdentifier($identifierID);
            unset($organizationData['identifier']);
            $country = Country::where('country_iso', $organizationData['country_id'])->first();
            if (!$country)
                $country = Country::findOrFail(228); //228 - id Украины
            $organizationData["country_id"] = $country->id;
            if ($organizationData["country_id"] == 228) {
                $tenderRegion = TendersRegions::find($organizationData['region_id']);
                $organizationData['region_name'] = ($tenderRegion) ? $tenderRegion->region_ua : '';
            }

            if (!$organization) {
                $organization = new Organization($organizationData);
                $organization->save();

                $scheme = Identifier::where('country_iso', $country->country_iso)->first();
                $organizationIdentifiers = new IdentifierOrganization([
                    'organisation_id' => $organization->id,
                    'identifier_id' => (isset($scheme)) ? $scheme->id : 0,
                    'identifier' => $identifierID
                ]);
                $organizationIdentifiers->save();
            } else {
                $organization->update($organizationData);
            }

            $data['organization_id'] = $organization->id;
            $data['tender_id'] = $request->get('tender_id');
        }

        $award->update($data);

        $this->_uploadDocs($award, $request->file('award')[array_keys($request->all()['award'])[0]]['files'], "/awards/{$award->id}/");
        Event::fire(new AwardSaveEvent($award));
        Session::flash('flash_message', 'Дані успішно оновлені');

        if ($award->tender->procedureType->procurement_method == 'limited' && $award->tender->procedureType->procurement_method_type != 'reporting') {
            return redirect()->route('award.list', $award->tender->id);
        }

        if ($award->tender->procedureType->procurement_method == 'open') {
            return redirect()->route('award.tender', $award->tender->id);
        }

        return redirect()->route('award.tender', $award->tender->id);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function lists(Request $requst, $id)
    {
        $awards = Award::where('tender_id', $id)->get();
        $tender = Tender::find($id);
        
        return view('pages.award.limited_lists', compact('awards', 'tender'));
    }

    /**
     * return 1 if exist at least 1 document and 0 if documents doesn`t exist
     *
     * @param $awardData
     * @return bool|int
     */
    protected function _existDocuments($awardData)
    {
        $filesCounter = 0;
        foreach ($awardData['award'] as $value){
            foreach ($value as $files){
                $filesCounter += count($files);
            }
        }
        if ($filesCounter >1) return 1;
        return 0;
    }

    /**
     * @param $tender
     * @param $awardData
     * @param $award
     */
    private function getMissingInfo($tender,  $award) {
        if($tender->type_id == 4 && $tender->cbd_id && empty($award->organization)) { // звит про укладений
            $api      = new Api();
            $supplier = $api->get($tender->cbd_id)['data']['awards'][0];

            $organization = \App\Import\Organization::getModel($supplier['suppliers'][0]);

            if($organization) {
                $awardData['organization_id'] = $organization->id;
                $award->update($awardData);
            }
        }
    }
    
    /*
     * performs simple validation if string looks like a signature
     */
    protected static function isValidSignature($sign)
    {
    	if (empty($sign)) return false;
    
    	$sign = base64_decode($sign);
    	if (!$sign) return false;
    
    	if (strlen($sign) < 15) return false;
    
    	// check if starts with SEQUENCE tag
    	$v = unpack('C2tag/nlen', $sign);
    	if ($v['tag1'] !== 0x30) return false;
    	if ($v['tag2'] !== 0x82) return false;
    	if ($v['len'] !== strlen($sign) - 4) return false;
    
    	$sign = substr($sign, 4);
    
    	// check if OID follows
    	$v = unpack('Ctype/Clen', $sign);
    	if ($v['type'] !== 0x06) return false;
    	if ($v['len'] !== 9) return false;
    
    	$oid = substr($sign, 2, 9);
    
    	// check if OID is 1.2.840.113549.1.7.2 (pkcs7-signedData)
    	if ($oid !== "\x2A\x86\x48\x86\xF7\x0D\x01\x07\x02") return false;
    
    	return true;
    }
    
    public function getSign(Request $request, $id)
    {
    	$award = Award::find($id);
    	$sign = $award->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();
    
    	$api = new Api(false);
    	$api->namespace = 'tenders';
    	$result = $api->get($award->tender->cbd_id, 'awards/' . $award->cbd_id, $award->tender->access_token);
    	if ($sign) {
    		$result['sign'] = file_get_contents($sign->url);
    	} else {
    		$result['sign'] = false;
    	}
    
    	return $result;
    }
    
    public function postSign(Request $request, $id)
    {
    	$sign = $request->get('sign');
    	if (self::isValidSignature($sign)) {
    
//    		if (Auth::user()->super_user) {
    			$award = Award::find($id);
//    		} else {
//    			$award = Auth::user()
//    			->organization
//    			->awards()->findOrFail($id);
//    		}
    
    		Storage::disk('documents')->put("/awards/$id/sign.p7s", $sign);
    		$document = $award->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();
    		if (!$document) {
    			$params = [
    					'title' => 'sign.p7s',
    					'description' => '',
    					'format' => 'application/pkcs7-signature',
    					'path' => "/awards/$id/sign.p7s",
    					'award_id' => $award->id,
    					'status' => 'new'
    			];
    
    			$newDocument = new AwardDocuments($params);
    			$award->documents()->save($newDocument);
    			
    			Event::fire(new AwardDocUploadEvent($newDocument));
    		} else {
    			$params = [
    					'title' => 'sign.p7s',
    					'description' => '',
    					'format' => 'application/pkcs7-signature',
    					'orig_id' => $document->orig_id,
    					'path' => "/awards/$id/sign.p7s",
    					'award_id' => $award->id,
    					'status' => 'new'
    			];
    			$document->status = 'old';
    			$document->save();
    
    			$existingDocument = new AwardDocuments($params);
    			$award->documents()->save($existingDocument);
    			
    			Event::fire(new AwardDocUploadEvent($existingDocument));
    		}
    		$award->signed = 1;
            $award->save();
    		
    		Session::flash('flash_message', 'Заявка на підтвердження переможця подана до ЦБД.');
    
    		//Event::fire(new TenderSaveEvent($tender));
    
    		return ['result' => 'success', 'message' => ''];
    	}
    
    	return ['result' => 'failed', 'message' => 'Підпис не переданий'];
    }
    
}
