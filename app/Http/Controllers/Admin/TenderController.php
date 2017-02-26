<?php

namespace App\Http\Controllers\Admin;

use App\Events\TenderAnswerEvent;
use App\Events\TenderPublishEvent;
use App\Model\Status;
use Validator;

use App\Events\DocumentUploadEvent;
use App\Events\TenderSaveEvent;
use App\Http\Requests\CreateTenderRequest;
use App\Model\Codes;
use App\Model\Document;
use App\Model\DocumentType;
use App\Model\Item;
use App\Model\Organization;
use App\Model\Tender;
use App\Model\TendersRegions;
use App\Model\Currencies;
use App\Model\Units;


use Illuminate\Http\Request;

use Event;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TenderController extends Controller
{
    protected function _uploadDocs($tender, $files, $path, $docTypes, $isNew = false)
    {
        if (is_array($files)) foreach ($files as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                $params = [
                    'document_parent_id' => $isNew ? $index : 0,
                    'title'       => '',
                    'description' => '',
                    'format'      => '',
                    'path'        => $path . $file->getClientOriginalName(),
                ];


                if ($isNew) {
                    $oldDoc = Document::find($index);
                    $params['type_id'] = $oldDoc->type_id;
                    $params['orig_id'] = $oldDoc->orig_id;
                } else {
                    $params['type_id'] = $docTypes[$index];
                }

                $newDoc = new Document($params);

                $tender->documents()->save($newDoc);

            }
        }
    }

    protected function _validate(Request $request)
    {
        $data = $request->all();
        $isOneGroup = 1;
        $i = 0;
        $firstCode = '';

        foreach ($data['items'] as $item) {
            foreach ($item['codes'] as $code) {
                $code = Codes::find($code)->first();
                if ($code && $code->type == 1) {
                    if ($i == 0) {
                        $firstCode = substr($code->code, 0, 3);
                        $i++;
                        continue;
                    }
                    $code = substr($code->code, 0, 3);
                    if ($firstCode != $code) {
                        $isOneGroup = 0;
                    }
                }
            }
        }

        $rules = [
            'is_cpv_one_group' => 'in:1',
            'title' => 'required|max:255',
            'description' => 'required',
            'amount' => 'required|numeric',
            'currency_id' => 'required|integer',
            'minimal_step' => 'required|numeric|max:'.$request->input('amount'),
            'enquiry_start_date' => 'required|date',
            'enquiry_end_date' => 'required|date|after:enquiry_start_date',
            'tender_start_date' => 'required|date|after:enquiry_end_date',
            'tender_end_date' => 'required|date|after:tender_start_date'
        ];

        $messages = [
            'minimal_step.max' => 'Минимальный шаг аукицона должен быть меньше чем сумма закупки.',
            'is_cpv_one_group.in' => 'Коды CPV должны быть из одной группы',
        ];
        $customAttributes = [];

        $itemFields = [
            'description' => 'Описание',
            'quantity' => 'Количество',
            'dkpp' => 'Код классификатора ДКПП',
            'cpv' => 'Код классификатора CPV',
            'unit_id' => 'Единица измерения',
            'delivery_date_start' => 'Начало периода поставки',
            'delivery_date_end' => 'Конец периода поставки',
            'codes.0.id' => 'Код CPV',
            'codes.1.id' => 'Код ДКПП',
        ];

        foreach ($request->all()['items'] as $index => $item) {
            foreach ($itemFields as $itemField => $description) {
                $rules["items.$index.$itemField"] = 'required';
                if ($itemField == 'quantity') {
                    $rules["items.$index.$itemField"] .= '|integer';
                    $messages["items.$index.$itemField.integer"] = "Поле \":attribute\" Лота №".($index + 1)." должно быть целым чилом.";
                }
                if ($itemField == 'codes.0.id' || $itemField == 'codes.1.id') {
                    $messages["items.$index.$itemField.required"] = ":attribute необходимо выбрать из списка";
                }
                if ($itemField != 'codes.0.id' && $itemField != 'codes.1.id') {
                    $messages["items.$index.$itemField.required"] = "Поле \":attribute\" Лота №" . ($index + 1) . "  необходимо заполнить.";
                }
                $customAttributes["items.$index.$itemField"] = $description;            }
        }

        $fields = $request->all();
        $fields['is_cpv_one_group'] = $isOneGroup;

        return Validator::make($fields, $rules, $messages, $customAttributes);
    }

    public function __construct() {
        $this->middleware('organization');
    }


    /**
     * Display a listing of the resource.
     *
     * @param $organizationWrongId
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $form = $request->get('form');
        if (empty($form)) {
            $form = ['mode' => ''];
        }
        $organizationId = $request->get('org_id');
        $searchStatus = $request->get('status');
        $sortField = $request->get('sf');
        $sortOrder = $request->get('so');
        if (empty($sortField)) {
            $sortField = 'created_at';
            $sortOrder = 'desc';
        }
        $statuses = Status::where('namespace', 'tender')->get();

        $tenders = Tender::our()->with(['organization' => function($query) {
            $query->where('source', 0)->take(1);
        }]);

        if (!empty($form['mode'])) {
            $tenders->where('mode', $form['mode']);
        }

        if (!empty($organizationId)) {
            $tenders = $tenders->where('organization_id', $organizationId);
        }
        if (!empty($searchStatus)) {
            $tenders = $tenders->where('status', $searchStatus);
        }

        $tenders = $tenders->orderBy($sortField, $sortOrder)->paginate(20);
        $sortOrder = $sortOrder == 'asc' ? 'desc' : 'asc';


        return view('admin.pages.tender.list', compact('tenders', 'organizationId', 'searchStatus', 'statuses', 'sortField', 'sortOrder', 'form'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @param $organizationWrongId
     *
     * @return Response
     */
    public function create()
    {

        abort(403);
        $documentTypes = DocumentType::whereNamespace('tender')->get();
        $regions = TendersRegions::orderBy('region_name')->lists('region_name', 'id');
        $currencies = Currencies::lists('currency_description', 'id');
        $units = Units::lists('description', 'id');
        $codes = Codes::whereParentId('0')->paginate(20);

        return view('pages.tender.create', compact('documentTypes', 'regions', 'currencies', 'units','codes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTenderRequest|Request $request
     *
     * @param                             $organizationWrongId
     *
     * @return Response
     */
    public function store(Request $request)
    {
        abort(403);
        $validator = $this->_validate($request);

        if ($validator->fails()) {
            return redirect('tender/create')
                ->withErrors($validator)
                ->withInput();
        }

        $tenderData = $request->all();
        $tenderData['mode'] = $request->user()->organization->mode;

        $tender = new Tender($tenderData);
        $request->user()->organization->tenders()->save($tender);

        foreach ($request->all()['items'] as $itemData) {
            $item = new Item($itemData);
            $tender->items()->save($item);
            $codes = [];
            foreach($itemData['codes'] as $code) {
                $codes[] = $code['id'];
            }
            $item->codes()->sync($codes);
        }

        $this->_uploadDocs($tender, $request->file('files'), "/tender/$tender->id/", $request->get('docTypes'));


        return redirect()->route('tender.index');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function show($id)
    {
        $tender = Tender::findOrFail($id);
        $user = Auth::user();

        return view('pages.tender.show-all-tender-info', compact('tender', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $organizationWrongId
     * @param $tenderWrongId
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function edit($id)
    {
        abort(403);
        $tender = Tender::with('items')->find($id);

        $documentTypes = DocumentType::whereNamespace('tender')->get();
        $regions = TendersRegions::orderBy('region_name')->lists('region_name', 'id');
        $currencies = Currencies::lists('currency_description', 'id');
        $units = Units::lists('description', 'id');

        return view('admin.pages.tender.edit', compact('tender', 'documentTypes', 'currencies', 'regions', 'units'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CreateTenderRequest|Request $request
     * @param                             $organizationWrongId
     * @param                             $tenderWrongId
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function update(CreateTenderRequest $request, $id)
    {
        abort(403);
        $validator = $this->_validate($request);

        if ($validator->fails()) {
            return redirect()->route('tender.edit', [$id])
                ->withErrors($validator)
                ->withInput();
        }

        $tender = Tender::find($id);

        $tender->update($request->all());
        foreach ($request->all()['items'] as $itemData) {
            if (isset($itemData['id']) && !empty($itemData['id'])) {
                $item = $tender->items->find($itemData['id']);
                $item->update($itemData);
            } else {
                $item = new Item($itemData);
                $tender->items()->save($item);
            }
            $codes = [];
            foreach($itemData['codes'] as $code) {
                $codes[] = $code['id'];
            }
            $item->codes()->sync($codes);
        }

        $this->_uploadDocs($tender, $request->file('files'), "/tender/$id/", $request->get('docTypes'));
        $this->_uploadDocs($tender, $request->file('newfiles'), "/tender/$id/", [], true);
        //Event::fire(new TenderSaveEvent($tender));
        foreach ($tender->documents as $doc) {
            if (empty($doc->url) && !empty($tender->cbd_id)) {
                Event::fire(new DocumentUploadEvent($doc));
            }
        }

        Session::flash('flash_message', 'Дані успішно оновлені');


        return redirect()->back();
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

    public function publish($id)
    {
        $tender = Tender::with('items')->findOrFail($id);
        Event::fire(new TenderSaveEvent($tender));

        return redirect()->back();
    }

    public function classifier(Request $request, $type)
    {
        $term = $request->input('term');
        return Codes::select(['id', \DB::raw('CONCAT(code, " ", description) AS value')])->where('type', $type)->where(function ($query) use ($term) {
            $query->where('code', 'LIKE', "$term%")
                ->orWhere('description', 'LIKE', "%$term%");
        })->get();
    }

    public function answer(Request $request, $tender)
    {
        Auth::user()->organization->tenders()->findOrFail($tender);
        $question = \App\Model\Question::find($request->input('id'));
        $textAnswer = ($request->input('answer'));
        $question->answer = $textAnswer;
        $question->save();
        Event::fire(new TenderAnswerEvent($question));

        return view('pages.question.below.answer', ['question' => $question]);
    }

}
