<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Events\PlanSaveEvent;
use App\Http\Requests;
use App\Model\Classifiers;
use App\Model\Codes;
use App\Model\Currencies;
use App\Model\Plan;
use App\Model\PlanDocument;
use App\Model\PlanItem;
use App\Model\ProcedureTypes;
use App\Model\Units;
use App\Services\FilterService\FilterService;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PlanningController extends Controller
{

    protected function _validate($data, $plan = null) {
        $rules = [
            'description' => 'required|max:512',
            'notes'       => 'required',
            'year'        => 'required',
            'currency_id' => 'required|integer',
            'amount'      => 'required|numeric',
            'start_month' => 'required',
            'start_year'  => 'required',
            'code'        => 'required',
            'code_id'     => 'required',
        ];

        $description = [
            'code.required'    => 'Введіть код класифікатора ДК 021:2015',
            'code_id.required' => 'Виберіть код класифікатора ДК 021:2015 з випадаючого списку',
            'amount'           => 'Очікувана вартість предмета закупівлі',
            'year'             => 'Рік',
        ];

        $notSpecifiedCodeId = 0;
        if (($plan && $plan->hasOneClassifier()) || (!$plan && time() > strtotime(env('ONE_CLASSIFIER_FROM')))) {

            /**
             * $notSpecifiedCodeId это айди опции "Не визначено" в випадающем списке на форме
             */
            $notSpecifiedCodeId = ($code = Codes::where('code', '99999999-9')->first()) ? $code->id : 0;
            if($data['code_id'] == $notSpecifiedCodeId) {
                $rules['code_additional']          = 'required';
                $rules['code_additional_id']       = 'required';
                $description['code_additional']    = 'Введіть код додаткового класифікатора';
                $description['code_additional_id'] = 'Виберіть код додаткового класифікатора з випадаючого списку';
                $codes[]                           = $data['code_additional_id'];
            }
        } else {
            $rules['code_additional']          = 'required';
            $rules['code_additional_id']       = 'required';
            $description['code_additional']    = 'Введіть код додаткового класифікатора';
            $description['code_additional_id'] = 'Виберіть код додаткового класифікатора з випадаючого списку';
            $codes[]                           = $data['code_additional_id'];
        }

        $codes[] = $data['code_id'];

        $validate = Validator::make($data, $rules, [], $description);
        if (!$validate->fails()) {
            if (isset($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $additionalClassifierIndex = 0;
                    foreach ($itemData['codes'] as $index => $code) {
                        $codes[] = $code['id'];
                        if ($index != 1)
                            $additionalClassifierIndex = $index;
                    }

                    $rules = [
                        'description' => 'required|max:512',
                        'quantity'    => 'required|numeric',
                        'unit_id'     => 'required',
                        'cpv'         => 'required',
                        'codes.1.id'  => 'required',
                    ];

                    $messages = [
                        'cpv.required'        => 'Введіть код класифікатора ДК 021:2015',
                        'codes.1.id.required' => 'Виберіть код класифікатора ДК 021:2015 з випадаючого списку',
                        'quantity.integer'    => "Поле \"Кількість\" повинно бути цілим числом.",
                    ];

                    $description = [
                        'description' => 'Назва номенклатури',
                        'quantity'    => 'Кількість',
                        'unit_id'     => 'Одиниці вимірювання',
                    ];

                    if (($plan && $plan->hasOneClassifier()) || (!$plan && time() > strtotime(env('ONE_CLASSIFIER_FROM')))) {
                        if ($itemData['codes'][1]['id'] == $notSpecifiedCodeId) {
                            $rules['dkpp']                                                    = 'required';
                            $rules['codes.' . $additionalClassifierIndex . '.id']             = 'required';
                            $messages['dkpp.required']                                        = 'Введіть код додаткового класифікатора';
                            $messages['codes.' . $additionalClassifierIndex . '.id.required'] = 'Виберіть код додаткового класифікатора з випадаючого списку';
                        }
                    } else {
                        $rules['dkpp']                                                    = 'required';
                        $rules['codes.' . $additionalClassifierIndex . '.id']             = 'required';
                        $messages['dkpp.required']                                        = 'Введіть код додаткового класифікатора';
                        $messages['codes.' . $additionalClassifierIndex . '.id.required'] = 'Виберіть код додаткового класифікатора з випадаючого списку';
                    }

                    $validate = Validator::make($itemData, $rules, $messages, $description);
                    if ($validate->fails())
                        return $validate;
                }
            }

            if (($plan && $plan->hasOneClassifier()) || (!$plan && time() > strtotime(env('ONE_CLASSIFIER_FROM'))))
                $validate = $this->_validateClassifiers($codes, 4);
            else
                $validate = $this->_validateClassifiers($codes);
        }

        return $validate;
    }

    protected function _validateClassifiers($codes = [], $charNum = 3) {
        $codes = Codes::whereIn('id', $codes)->get();
        $cpv = $additional = $cpvAll = $additionalAll = [];
        foreach ($codes as $code) {
            $add = false;
            if ($code->type == 1) {
                $currentCode = substr($code->code, 0, $charNum);
                if (!empty($cpvAll)) {
                    foreach ($cpvAll as $key => $val) {
                        if (strpos($currentCode, $key) !== 0 && strpos($key, $currentCode) !== 0) {
                            $add = true;
                            break;
                        }
                    }
                } else {
                    $add = true;
                }

                $cpvAll[$currentCode] = 1;
                if ($add)
                    $cpv[$currentCode] = 1;
            } else {
                $currentCode = substr($code->code, 0, 7);
                if (!empty($additionalAll)) {
                    foreach ($additionalAll as $key => $val) {
                        if (strpos($currentCode, $key) !== 0 && strpos($key, $currentCode) !== 0) {
                            $add = true;
                            break;
                        }
                    }
                } else {
                    $add = true;
                }

                $additionalAll[$currentCode] = 1;
                if ($add)
                    $additional[$currentCode] = 1;
            }
        }

        $data['cpv'] = count($cpv);
        $data['additional'] = (count($additional) == 0) ? 1 : count($additional);

        $rules = [
            'cpv'           => 'in:1',
            'additional'    => 'in:1'
        ];

        $messages = [
            'cpv.in'           => 'У всіх номенклатурах мають співпадати перші три символи коду класифікатора ДК 021:2015',
            'additional.in'    => 'У всіх номенклатурах мають співпадати перші п\'ять символів додаткового коду класифікатора'
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Classifiers $classifier
     * @return \Illuminate\Http\Response
     */
    public function create(Classifiers $classifier)
    {
        $currencies     = Currencies::lists('currency_description', 'id');
        $units          = Units::lists('description', 'id');
        $codes          = Codes::paginate(20);
        $classifiers    = $classifier->getNewClassifiersList();
        $procedureTypes = ProcedureTypes::where('active', 1)->orWhere('id', 7)->lists('procedure_name', 'id');

        //TODO вынести в хелперс
        $days = ['0' => 'не визначено'];
        for ($i = 1; $i < 32; $i++) {
            $index = ($i > 9) ? $i : '0'.$i;
            $days[$index] = $i;
        }
        $months = [
            '01' => 'січень',
            '02' => 'лютий',
            '03' => 'березень',
            '04' => 'квітень',
            '05' => 'травень',
            '06' => 'червень',
            '07' => 'липень',
            '08' => 'серпень',
            '09' => 'вересень',
            '10' => 'жовтень',
            '11' => 'листопад',
            '12' => 'грудень'
        ];
        $curYear = date('Y', strtotime('-5 days'));
        for ($i = 0; $i < 11; $i++)
            $years[$curYear + $i] = $curYear + $i;

        return view('pages.planning.create', compact('currencies', 'units', 'codes', 'classifiers', 'procedureTypes', 'days', 'months', 'years'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = $this->_validate($data);

        if ($validator->fails()) {
            return redirect('plan/create')
                ->withErrors($validator)
                ->withInput();
        }
        if (intval($data['start_day']) > 0) {
            $data['start_date'] = $data['start_day'] . '.' . $data['start_month'] . '.' . $data['start_year'];
        } else {
            $data['start_date'] = '01.' . $data['start_month'] . '.' . $data['start_year'];
        }
        if (Auth::check() && !Auth::user()->organization->mode)
            $data['description'] = '[ТЕСТУВАННЯ] ' . $data['description'];

        $data['mode'] = $request->user()->organization->mode;
        $plan = new Plan($data);
        $request->user()->organization->plans()->save($plan);

        if (isset($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $item = new PlanItem($itemData);
                $plan->items()->save($item);
                $codes = [];
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
                $item->codes()->sync($codes);
            }
        }

        if (is_array($data['plan']['files'])) foreach ($data['plan']['files'] as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put("/plan/{$plan->id}/" . $file->getClientOriginalName(), File::get($file));
                $params = [
                    'format' => '',
                    'path' => "/plan/{$plan->id}/" . $file->getClientOriginalName(),
                    'plan_id' => $plan->id
                ];
                $newDoc = new PlanDocument($params);

                $plan->documents()->save($newDoc);

            }
        }

        Event::fire(new PlanSaveEvent($plan));

        return redirect()->route('plan.list');
    }

    public function lists(Request $request)
    {
        $listName = 'plans-list';
        $filters = $this->_createFilter($listName, '/plan/filter', 'plans');

        $plans = $request->user()->organization->plans()->orderBy('created_at', 'DESC')->paginate(20);
        return view('pages.planning.user_list', compact('plans', 'filters', 'listName'));
    }

    /**
     * @param string $name
     * @param string $url
     * @param string $table
     * @return string
     */
    private function _createFilter($name, $url, $table)
    {
        $model = new Plan();
        $procedure = new ProcedureTypes();
        $filterService = new FilterService($table);

        $filterService->setTextField($table, 'id', $model->_getLabel('id'));
        $filterService->setTextField($table, 'planID', $model->_getLabel('planID'));
        $filterService->setTextField($table, 'description', $model->_getLabel('description'));
        $filterService->setTextField($table, 'start_date', $model->_getLabel('start_date'), FilterService::DATE_LIKE_TYPE);
        $filterService->setListField($table, 'procedure_id', $model->_getLabel('procedure_name'), FilterService::TEXT_TYPE,
            $procedure->getAllProceduresArray());
        $filterService->setPeriodField($table, 'amount', $model->_getLabel('amount'), FilterService::PRICE_TYPE);

        return $filterService->create($name, $url);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filter(Request $request)
    {
        if($request->ajax()) {
            $service = new FilterService('plans');
            $model = $service->createFilterRequest(Auth::user()->organization->plans(), $request->all());
            $plans = $model->orderBy('created_at', 'DESC')->paginate(20);
            return view('pages.planning._part.list', compact('plans'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Auth::user()->super_user) {
            $plan = Plan::find($id);
        } else {
            $plan = Auth::user()
                ->organization
                ->plans()->findOrFail($id);
        }
        return view('pages.planning.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @param Classifiers $classifier
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Classifiers $classifier, $id)
    {
        if (Auth::user()->super_user) {
            $plan = Plan::find($id);
        } else {
            $plan = Auth::user()
                ->organization
                ->plans()->findOrFail($id);
        }

        $currencies     = Currencies::lists('currency_description', 'id');
        $units          = Units::lists('description', 'id');
        $codes          = Codes::paginate(20);
        $classifiers    = $classifier->getClassifiersList();
        $procedureTypes = ProcedureTypes::where('active', 1)->orWhere('id', 7)->lists('procedure_name', 'id');
        $days = ['0' => 'не визначено'];
        //TODO вынести в хелперс
        for ($i = 1; $i < 32; $i++) {
            $index = ($i > 9) ? $i : '0'.$i;
            $days[$index] = $i;
        }
        $months = [
            '01' => 'січень',
            '02' => 'лютий',
            '03' => 'березень',
            '04' => 'квітень',
            '05' => 'травень',
            '06' => 'червень',
            '07' => 'липень',
            '08' => 'серпень',
            '09' => 'вересень',
            '10' => 'жовтень',
            '11' => 'листопад',
            '12' => 'грудень'
        ];
        $curYear = date('Y');
        for ($i = 0; $i < 11; $i++)
            $years[$curYear + $i] = $curYear + $i;

        return view('pages.planning.edit', compact('plan', 'currencies', 'units', 'codes', 'classifiers', 'procedureTypes', 'days', 'months', 'years'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->super_user) {
            $plan = Plan::find($id);
        } else {
            $plan = Auth::user()
                ->organization
                ->plans()->findOrFail($id);
        }

        $data = $request->all();
        $validator = $this->_validate($data, $plan);
        if ($validator->fails()) {
            return redirect()->route('plan.edit', [$id])
                ->withErrors($validator)
                ->withInput();
        }

        if (Auth::check() && !Auth::user()->organization->mode && strpos($data['description'], '[ТЕСТУВАННЯ]') === false)
            $data['description'] = '[ТЕСТУВАННЯ] ' . $data['description'];

        $plan->update($data);

        if (isset($data['items'])) {
            $itemIds = [];
            foreach ($data['items'] as $itemData) {
                if (isset($itemData['id']) && !empty($itemData['id'])) {
                    $item = $plan->items->find($itemData['id']);
                    $item->update($itemData);
                }else {
                    $item = new PlanItem($itemData);
                    $plan->items()->save($item);
                }
                $codes = [];
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
                $item->codes()->sync($codes);
                $itemIds[] = $item->id;
            }

            $plan->items()->whereNotIn('id', $itemIds)->delete();
        }

        Event::fire(new PlanSaveEvent($plan));

        Session::flash('flash_message', 'Дані успішно оновлені');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
        $plan = Plan::find($id);
        $sign = $plan->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();

        $api = new Api(false);
        $api->namespace = 'plans';
        $result = $api->get($plan->cbd_id);
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


            //TODO-PARUS может что-то поломал
            if($request->get('id') && \App\Model\Organization::find($request->get('id'))->source == 2) { // парус
                $plan = \App\Model\Organization::find($request->get('id'))->plans()->findOrFail($id);
            } else if (Auth::user()->super_user) { // админы
                $plan = Plan::find($id);
            } else { // все остальные
                $plan = Auth::user()
                    ->organization
                    ->plans()->findOrFail($id);
            }

            Storage::disk('documents')->put("/plan/$id/sign.p7s", $sign);
            $document = $plan->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();
            if (!$document) {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'path' => "/plan/$id/sign.p7s",
                    'plan_id' => $plan->id
                ];

                $newDocument = new PlanDocument($params);
                $plan->documents()->save($newDocument);
            } else {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'orig_id' => $document->orig_id,
                    'path' => "/plan/$id/sign.p7s",
                    'tender_id' => $plan->id
                ];

                $existingDocument = new PlanDocument($params);
                $plan->documents()->save($existingDocument);
            }

            Event::fire(new PlanSaveEvent($plan));

            return ['result' => 'success', 'message' => ''];
        }

        return ['result' => 'failed', 'message' => 'Підпис не переданий'];
    }

    /**
     * convers xls to plans
     *
     * @param Request $request
     */
    public function importPlan(Request $request) {


        $xlsRows = Excel::load($request->file('xlsFile'))->get();

        $months = [
            'січень'   => '01',
            'лютий'    => '02',
            'березень' => '03',
            'квітень'  => '04',
            'травень'  => '05',
            'червень'  => '06',
            'липень'   => '07',
            'серпень'  => '08',
            'вересень' => '09',
            'жовтень'  => '10',
            'листопад' => '11',
            'грудень'  => '12',
        ];

        $dk2015 = '/[0-9]{8}[-][0-9]{1}/'; //восемь цифр тире цифра

        $rules = [
            'description'                => 'required',
            'year'                       => 'required',
            'start_month'                => 'required',
            'start_year'                 => 'required',
            'procedure_id'               => 'required',
            'amount'                     => 'required',
            'code_id'                    => 'required',
            'notes'                      => 'required',
            'code_id_pattern'            => 'dk2015',
            'code_additional_id_pattern' => 'dk2010',
        ];

        $planCounter = 1; //показывает порядковый номер плана
        foreach($xlsRows as $xlsRow) {

            if($this->_isNextRowEmpty($xlsRow)) break;

            $messages = [
                'description.required'   => 'Поле "Предмет закупівлі" не може бути пустим в рядку № ' . (string)(7 + $planCounter),
                'year.required'          => 'Поле "Орієнтовний початок проведення процедури закупівлі" не може бути пустим  в рядку № ' . (string)(7 + $planCounter),
                'start_month.required'   => 'Поле "Орієнтовний початок проведення процедури закупівлі" не може бути пустим  в рядку № ' . (string)(7 + $planCounter),
                'start_year.required'    => 'Поле "Орієнтовний початок проведення процедури закупівлі" не може бути пустим  в рядку № ' . (string)(7 + $planCounter),
                'procedure_id.required'  => 'Поле "Процедура закупівлі" не може бути пустим  в рядку № ' . (string)(7 + $planCounter),
                'amount.required'        => 'Поле "Очікувана вартість закупівлі  грн. (в т.ч.ПДВ)" не може бути пустим в рядку № ' . (string)(7 + $planCounter),
                'code_id.required'       => 'Поле "Предмет закупівлі" не може бути пустим та повинно містити ДК 021:2015 у форматі "00000000-0" (перевірте кількість цифр у ДК 021:2015 їх повинно бути 8) в рядку № ' . (string)(7 + $planCounter),
                'code_id_pattern.dk2015' => 'Невірний формат ДК 021:2015 в рядку № ' . (string)(7 + $planCounter) . ' Дотримуйтесь формату "00000000-0", тобто вісім цифр, тире, цифра в рядку № ' . (string)(7 + $planCounter),
                'notes.required'         => 'Поле "Примітки" не може бути пустим в рядку № ' . (string)(7 + $planCounter),
            ];

            preg_match($dk2015, $xlsRow[1], $code_id);

            $code_id_pattern            = $code_id[0];

            $code_id = Codes::where('code', 'like', $code_id[0])->first() ? Codes::where('code', 'like', $code_id[0])->first()->id : null;

            if(isset($additional_code_id[0])) {
                $code_additional_id_pattern = $additional_code_id[0];
                $additional_code_id         = Codes::where('code', 'like', $additional_code_id[0])->first() ? Codes::where('code', 'like', $additional_code_id[0])->first()->id : null;
            } else {
                $code_additional_id_pattern = '';
                $additional_code_id         = '';
            }

            $planData = [
                'description'                => $xlsRow[1],
                'year'                       => explode(' ', $xlsRow[5])[1],
                'start_month'                => $months[mb_strtolower(explode(' ', $xlsRow[5])[0])],
                'start_year'                 => explode(' ', $xlsRow[5])[1],
                'procedure_id'               => ProcedureTypes::where('procedure_name', 'like', '%'.trim($xlsRow[4], ' ').'%')->first()->id,
                'amount'                     => $xlsRow[3],
                'code_id'                    => $code_id,
                'code_additional_id'         => $additional_code_id,
                'notes'                      => $xlsRow[6],
                'currency_id'                => 1,
                'start_date'                 => '01.' . $months[mb_strtolower(explode(' ', $xlsRow[5])[0])] . explode(' ', $xlsRow[5])[1],
                'code_id_pattern'            => $code_id_pattern,
                'code_additional_id_pattern' => $code_additional_id_pattern,
            ];

            //не обязательный кекв
            if(!empty($xlsRow[2])) {
                $kekv_id                  = Codes::where('code', 'like', $xlsRow[2])->first()->id;
                $planData['code_kekv_id'] = $kekv_id;
            }

            $validate = Validator::make($planData, $rules, $messages);

            if($validate->fails()) {

                return redirect('plan/import/')
                    ->withErrors($validate)
                    ->withInput();

            }

            $plans[] = $planData;

            $planCounter++;

            /**
             * 7 номер строки xls файла с которой Illuminate\Support\Facades\Facade начинает читать файл
             * в файле config/excel.php startRow = '7'
             */


        } // END FOR EACH

        foreach($plans as $planData) {
            $plan = new Plan($planData);
            Auth::user()->organization->plans()->save($plan);
            Event::fire(new PlanSaveEvent($plan));
        }

        Session::flash('flash_message', 'Плани були успішно імпортовані');

        return redirect()->back();
    }

    public function createImport() {
        return view('pages.planning.planImport');
    }


    private function _isNextRowEmpty($xlsRow) {
        $isNextRowEmpty = false;
        $counter = 0;

        foreach ($xlsRow as $rowValue) {
            if($rowValue == null) {
                $counter++;
            }

            if($counter > 2) {
                $isNextRowEmpty = true;
                break;
            }
        }

        return $isNextRowEmpty;
    }
}
