<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Events\AwardDocUploadEvent;
use App\Events\AwardSaveEvent;
use App\Events\BidDeleteEvent;
use App\Events\BidDocUploadEvent;
use App\Events\BidSaveEvent;
use App\Events\QualificationDocUploadEvent;
use App\Events\QualificationSaveEvent;
use App\Events\ReturnMoneyForInvalidBidEvent;
use App\Http\Requests;
use App\Jobs\ChangeStatus;
use App\Jobs\SyncBid;
use App\Listeners\ReturnMoneyForInvalidBidListener;
use App\Model\Award;
use App\Model\AwardDocuments;
use App\Model\Bid;
use App\Model\BidDocuments;
use App\Model\DocumentType;
use App\Model\Feature;
use App\Model\GroundsForRejection;
use App\Model\History;
use App\Model\Lot;
use App\Model\Notification;
use App\Model\ProcedureTypes;
use App\Model\Qualification;
use App\Model\QualificationDocuments;
use App\Model\RejectReason;
use App\Model\Status;
use App\Model\Tender;
use App\Payments\Payments;
use App\Services\FilterService\FilterService;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Carbon\Carbon;
use Chumper\Zipper\Zipper;
use Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use SoapBox\Formatter\Formatter;

class BidController extends Controller
{
    const DEFAULT_LANGUAGE = 'ua';

    protected function _uploadDocs($bid, $request, $path)
    {
        $arFiles['edit'] = $request->file('newfiles');
        $arFiles['add'] = $request->file('bid')['files'];

        foreach ($arFiles as $action => $files) {
            if (is_array($files)) {
                foreach ($files as $index => $file) {
                    if ($file) {
                        Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                        $params = [
                            'path' => $path . $file->getClientOriginalName()
                        ];

                        if ($action == 'edit') {
                            $oldDoc = BidDocuments::find($index);
                            BidDocuments::where('orig_id', $oldDoc->orig_id)->where('bid_id', $oldDoc->bid_id)->update(['status' => 'old']);
                            $params['type_id'] = $oldDoc->type_id;
                            $params['orig_id'] = $oldDoc->orig_id;
                            $params['bid_id'] = $oldDoc->bid_id;
                            $params['confidential'] = $oldDoc->confidential;
                            $params['confidential_cause'] = $oldDoc->confidential_cause;
                            $params['description_decision'] = $oldDoc->description_decision;

                            //$oldDoc->status = 'old';
                            //$oldDoc->update();
                        } else {
                            $params['type_id'] = $request->input('bid')['docTypes'][$index];
                            $params['confidential'] = isset($request->input('bid')['confidential'][$index]) ? $request->input('bid')['confidential'][$index] : false;
                            $params['confidential_cause'] = isset($request->input('bid')['confidentialCause'][$index]) ? $request->input('bid')['confidentialCause'][$index] : false;
                            $params['description_decision'] = (isset($request->input('bid')['description_decision'][$index]) == 'on') ? 1 : 0;
                        }

                        $newDoc = new BidDocuments($params);
                        $bid->documents()->save($newDoc);
                    }
                }
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function listing()
    {
        $listName = 'bids-list';
        $filters = $this->_createFilter($listName, '/bid/filter', 'bid');

        $bids = Auth::user()->organization->bids()->withTrashed()->orderBy('created_at', 'DESC')->paginate(20);

        return view('pages.bids.user_list', compact('bids','filters','listName'));
    }

    /**
     * @param string $name
     * @param string $url
     * @param string $table
     * @return string
     */
    private function _createFilter($name, $url, $table)
    {
        $statuses = new Status();
        $filterService = new FilterService($table);

        $filterService->setTextField('tender', 'tenderID', 'Ідентифікатор');
        $filterService->setTextField('tender', 'title', 'Найменування');
        $filterService->setListField($table, 'status', 'Статус пропозиції', FilterService::TEXT_TYPE,
            $statuses->getAllStatuses($table));
        $filterService->setListField('tender', 'status', 'Статус закупівлі', FilterService::TEXT_TYPE,
            $statuses->getAllStatuses('tender'));
        $filterService->setListField('award', 'status', 'Статус переможців', FilterService::TEXT_TYPE,
            $statuses->getAllStatuses('award'));
        $filterService->setListField('qualification', 'status', 'Статус кваліфікації', FilterService::TEXT_TYPE,
            $statuses->getAllStatuses('qualification'));
        $filterService->setPeriodField($table, 'amount', 'Бюджет', FilterService::PRICE_TYPE);
        $filterService->setPeriodField('tender', 'auction_start_date', 'Дата (закупівля)', FilterService::DATE_TYPE);
        $filterService->setPeriodField('lot', 'auction_start_date', 'Дата (лот)', FilterService::DATE_TYPE);

        return $filterService->create($name, $url);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filter(Request $request)
    {
        if($request->ajax()) {
            $service = new FilterService('bid');
            $model = $service->createFilterRequest(Auth::user()->organization->bids(), $request->all());
            $bids = $model->where('organization_id', Auth::user()->organization->id)->withTrashed()->orderBy('bids.created_at', 'DESC')->paginate(20);
            return view('pages.bids._part.list', compact('bids'));
        }
    }

    /**
     * Display a listing of the user resource.
     *
     * @return Response
     */
    public function index($id)
    {
        $tender = Tender::find($id);
        if ($tender->status == 'cancelled') {
            return redirect()->route('tender.show', [$id]);
        }

        $documentTypes = DocumentType::whereNamespace('bid')->lists('document_type', 'id');
        $qualificationDocumentTypes = DocumentType::whereNamespace('qualification')->lists('document_type', 'id');

        $grounds = '';
        $groundsForRejections = GroundsForRejection::prequalification()->get();
        foreach ($groundsForRejections as $ground) {
            $grounds['titles'][$ground->id] = $ground->title;
            $grounds['descriptions'][$ground->id] = $ground->description;
        }

        return view('pages.bids.list', ['tender' => $tender, 'groundsForRejections' => $grounds, 'documentTypes' => $documentTypes, 'qualificationDocumentTypes' => $qualificationDocumentTypes]);
    }

    public function rejectForm($id)
    {
        $award = Award::find($id);
        $user = $award->tender->organization->user()->find(Auth::user()->id);
        //$tender = $award->tender;
        //$tender->signed = 0;
        //$tender->save();
        if (!$user) {
            abort(403);
        }

        $grounds = [];
        if ($award->tender->procedureType->procurement_method_type == 'aboveThresholdUA.defense') {
            $groundsForRejections = RejectReason::defense()->pending()->get();
        } else {
            $groundsForRejections = GroundsForRejection::prequalification()->get();
        }
        foreach ($groundsForRejections as $ground) {
            $grounds['titles'][$ground->id] = $ground->title;
            $grounds['descriptions'][$ground->id] = str_replace(array("\r", "\n"), "", $ground->description);
        }

        return view('pages.award.qualify', ['award' => $award, 'tender' => $award->tender, 'status' => 'reject', 'groundsForRejections' => $grounds]);
    }

    public function reject(Request $request, $id)
    {
        $requestData = $request->all();
        $status = empty($request->get('s')) ? 'unsuccessful' : 'cancelled';
        $award = Award::find($id);
        $user = $award->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        if ($award->documents()->count() == 0 && $status == 'unsuccessful') {
            Session::flash('flash_error', 'Для відхилення кандидата додайте файл рішення та натисніть Зберегти');
            if ($award->tender->procedureType->procurement_method == 'limited' && $award->tender->procedureType->procurement_method_type != 'reporting') {
                return redirect()->route('award.list', [$award->tender->id]);
            }
            return redirect()->route('award.tender', [$award->tender->id]);

//            return redirect()->back();//route('award.edit', [$award->id]);
//            return redirect()->route('award.list', [$award->tender->id]);
        }

        if ($award->tender->procedureType->procurement_method == 'open' && $award->tender->procedureType->procurement_method_type != 'belowThreshold' && $status == 'unsuccessful') {
            if (empty($requestData['unsuccessful_title']) || empty($requestData['unsuccessful_description'])) {
                Session::flash('flash_error', 'Причина і опис рішення - обов\'язкові для заповнення');
                return redirect()->back()->withInput(Input::all());
            }
            $award->unsuccessful_title = json_encode($requestData['unsuccessful_title']);
            $award->unsuccessful_description = $requestData['unsuccessful_description'];
            $award->qualified = 0;
            $award->eligible = 0;
        }

        $award->status = ($status=='unsuccessful')?'unsuccessfully':'cancelled';


        if (($status=='cancelled')||(in_array($award->tender->procedureType->procurement_method_type,['reporting', 'negotiation', 'negotiation.quick', 'belowThreshold']))) {
        	Event::fire(new AwardSaveEvent($award));
        	$status = ($status=='cancelled') ? $status:'unsuccessful';
        	$this->dispatch(new ChangeStatus($award, $status));
        	Session::flash('flash_message', 'Заявка на відміну переможця подана до ЦБД.');
        }
        
		$award->save();

        if ($award->tender->procedureType->procurement_method == 'limited') {
            return redirect()->route('award.list', [$award->tender->id]);
        }

        return redirect()->route('award.tender', [$award->tender->id]);
    }

    public function confirmForm($id)
    {
        $award = Award::find($id);
        $user = $award->tender->organization->user()->find(Auth::user()->id);
        //$tender = $award->tender;
        //$tender->signed = 0;
        //$tender->save();
        if ($award->documents()->count() == 0 && $award->tender->procedureType->procurement_method_type != 'reporting') {
        	Session::flash('flash_error', 'Для визначення переможцем кандидата додайте файл рішення та натисніть Зберегти');
        	if ($award->tender->procedureType->procurement_method == 'open') {
        		return redirect()->route('award.tender', [$award->tender->id]);
        	} else {
        		if ($award->tender->procedureType->procurement_method == 'limited' && $award->tender->procedureType->procurement_method_type != 'reporting') {
        			return redirect()->route('award.list', [$award->tender->id]);
        		}
        		return redirect()->route('award.edit', [$award->id]);
        	}
        }
        
        if (!$user) {
            abort(403);
        }

        return view('pages.award.qualify', ['award' => $award, 'tender' => $award->tender, 'status' => 'confirm']);
    }

    public function confirm(Request $request, $id)
    {
        $requestData = $request->all();
        $award = Award::find($id);
        $user = $award->tender->organization->user()->find(Auth::user()->id);
        if (!$user && !Auth::user()->super_user) {
            abort(403);
        }

        if (empty($award->cbd_id)) {
            Session::flash('flash_error', 'Дані переможця ще завантажуються до центральної бази даних. Зачекайте, будь ласка, 10 хвилин та оновіть сторінку. Якщо ситуація не змінилась, зверніться до служби підтримки.');
            if ($award->tender->procedureType->procurement_method == 'open') {
                return redirect()->route('award.tender', [$award->tender->id]);
            } else {
                if ($award->tender->procedureType->procurement_method == 'limited' && $award->tender->procedureType->procurement_method_type != 'reporting') {
                    return redirect()->route('award.list', [$award->tender->id]);
                }
                return redirect()->route('award.edit', [$award->id]);
            }
        }

        if ($award->documents()->count() == 0 && $award->tender->procedureType->procurement_method_type != 'reporting') {
            Session::flash('flash_error', 'Для визначення переможцем кандидата додайте файл рішення та натисніть Зберегти');
            if ($award->tender->procedureType->procurement_method == 'open') {
                return redirect()->route('award.tender', [$award->tender->id]);
            } else {
                if ($award->tender->procedureType->procurement_method == 'limited' && $award->tender->procedureType->procurement_method_type != 'reporting') {
                    return redirect()->route('award.list', [$award->tender->id]);
                }
                return redirect()->route('award.edit', [$award->id]);
            }
        }

        if (($award->tender->procedureType->procurement_method == 'open' && $award->tender->procedureType->procurement_method_type != 'belowThreshold') || $award->tender->procedureType->procurement_method == 'selective') {
            if (!((isset($requestData['qualified']) && isset($requestData['eligible'])))) {
                Session::flash('flash_error', 'Підтвердіть, що пропозиція відповідає кваліфікаційним критеріям, встановленим замовником в тендерній документації та
відсутні підстави для відмови в участі згідно ст. 17 Закону України ”Про Публічні закупівлі”');
                return redirect()->back()->withInput(Input::all());
            }
            $award->unsuccessful_title = null;
            $award->unsuccessful_description = null;
            $award->qualified = 1;
            $award->eligible = 1;
        }

        $award->status = 'activate';
        $award->save(); 
        
        if (in_array($award->tender->procedureType->procurement_method_type,['reporting', 'negotiation', 'negotiation.quick', 'belowThreshold'])){
        	Event::fire(new AwardSaveEvent($award));
        	$this->dispatch(new ChangeStatus($award, 'active'));
        	Session::flash('flash_message', 'Заявка на підтвердження переможця подана до ЦБД.');
        }

        if ($award->tender->procedureType->procurement_method == 'limited') {
            return redirect()->route('award.list', [$award->tender->id]);
        }

        if ($award->tender->type_id == 4) {
            return redirect()->route('award.show', [$award->id]);
        }

        return redirect()->route('award.tender', [$award->tender->id]);
    }

    public function upload(Request $request, $id)
    {
        $award = Award::find($id);
        $user = $award->bid->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        foreach ($request->file('files') as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put("/award/$id/" . $file->getClientOriginalName(), File::get($file));
                $params = [
                    'path'        => "/award/$id/" . $file->getClientOriginalName(),
                ];

                $newDoc = new AwardDocuments($params);

                $award->documents()->save($newDoc);

                Event::fire(new AwardDocUploadEvent($newDoc));
            }
        }

        return redirect()->route('tender.bids', [$award->bid->tender->id]);
    }

    public function uploadDoc(Request $request, $id)
    {
        return [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param $entityName
     * @param $id
     * @return Response
     * @internal param $tenderId
     */
    public function create(Request $request, $entityName, $id)
    {
        if ($entityName == 'tender') {
            $entity = Tender::with(['lots', 'features'])->findOrFail($id);
        } else {
            $entity = Lot::with(['tender', 'features'])->findOrFail($id);
        }

        if ($entity->tender->procedureType->threshold_type == 'above') {
            if (empty(Auth::user()->organization->legal_name) || empty(Auth::user()->organization->legal_name_en)) {
                Session::flash('flash_modal', 'Вкажіть повну юридичну назву вашої організації.');
                return redirect()->route('organization.edit');
            }
        }

        $hasBids = $entity->bids()->where('organization_id', Auth::user()->organization->id)->first();
        if ($hasBids) {
            return redirect()->route('bid.edit', [$hasBids->id]);
        }

        if ($entity->tender->mode == 1 && Auth::user()->organization->mode == 0) {
            \Session::flash('flash_modal', trans('messages.agreement.supplier'));

            return redirect()->route('tender.show', [$entity->tender->id]);
        }
        $features = new Collection();
        foreach ($entity->tender->features as $feature) {
            $features->add($feature);
        }
        if ($entityName == 'lot') {
            foreach ($entity->features as $feature) {
                $features->add($feature);
            }
            foreach ($entity->items()->get() as $item) {
                foreach ($item->features as $feature) {
                    $features->add($feature);
                }
            }
        }

        $allValues = ['' => ''];
        foreach ($features as $feature) {
            foreach ($feature->values as $value) {
                $allValues[$value->id] = $value->value;
            }

        }
        $documentTypes = DocumentType::whereNamespace('bid')->get();
        $paymentAmount = Payments::getPrice($entity->amount);
        $balance = Payments::balance()->amount;
        $tenderDateEnd = date('m/d/Y H:i:s',strtotime($entity->tender->tender_end_date));
        return view('pages.bids.create', [
            'entity' => $entity,
            'features' => $features,
            'allValues' => collect($allValues),
            'tenderDateEnd'=>$tenderDateEnd,
            'paymentAmount'=>$paymentAmount,
            'balance' => $balance,
            'documentTypes' => $documentTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */

    public function store(Request $request)
    {
        $entityName = $request->input('entity_type');
        $entityId = $request->input('entity_id');
        if ($entityName == 'tender') {
            $entity = Tender::findOrFail($entityId);
        } else {
            $entity = Lot::with('tender')->findOrFail($entityId);
        }
        if ($entity->tender->procedureType->threshold_type != 'below' && count($request->file('bid')['files']) < 2) {
            Session::flash('flash_error', 'Для подачі пропозиції додайте документи та натисніть Подати пропозицію.');
            return redirect()->back()->withInput();
        }

        $hasBids = $entity->bids()->where('organization_id', Auth::user()->organization->id)->first();
        if ($hasBids) {
            return redirect()->route('bid.edit', [$hasBids->id]);
        }

        if ($entity->tender->mode == 1 && Auth::user()->organization->mode == 0) {
            \Session::flash('flash_modal', trans('messages.agreement.supplier'));

            return redirect()->route('tender.show', [$entity->tender->id]);
        }


        if (!$this->timeValidation($entity->tender)){
            return redirect()->back();
        }
        $rules = [
            'amount' => 'required',
            'less' => 'in:1',
        ];
        if (is_object($entity->tender->procedureType) && ($entity->tender->procedureType->procurement_method == 'open' && $entity->tender->procedureType->procurement_method_type != 'belowThreshold')) {
            $rules['self_qualified'] = 'required';
            $rules['self_eligible'] = 'required';
        }
        $requestData = $request->all();

        if ($requestData['amount'] <= $entity->amount) {
            $requestData['less'] = 1;
        } else {
            $requestData['less'] = 0;
        }

        $messages = [
            'amount.required' => 'Вкажіть суму пропозиції',
            'less.in' => "Сума пропозиції не повинна перевищувати очікувану вартість ".($entityName == 'tender' ? 'закупвілі' : 'лоту'),
            'self_qualified.required' => "Необхідно підтвердити відповідність квалифікаційним критеріям",
            'self_eligible.required' => "Необхідно підтвердити відсутність підстав для відмови в участі",
        ];
        if (isset($requestData['values'])){
            $requestData['val'] = $requestData['values'][0]['id'];
            $rules['val'] = 'required';
            $messages['val.required'] ='Виберіть неціновий показник';
        }

        if (isset($requestData['bid']['confidential'])) {
            foreach ($requestData['bid']['confidential'] as $index => $confidential) {
                if ($confidential && isset($requestData['bid']['confidentialCause']) && strlen($requestData['bid']['confidentialCause'][$index]) < 30) {
                    $rules['confidentialCause[]'] = 'required';
                    $messages['confidentialCause[].required'] = 'Мінімальна довжина причини конфіденційності - 30 символів.';
                }
            }
        }
        $validator = Validator::make($requestData, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('bid.new', [$entityName, $entityId])
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $paymentAmount = Payments::getPrice($entity->amount);

            if ($entity->tender->mode == 1) {
                Payments::minus($paymentAmount, $entity);
            }
            $bid = new \App\Model\Bid([
                'amount' => $request->input('amount'),
                'self_qualified' => ($request->input('self_qualified') == 'on') ? 1 : 0,
                'self_eligible' => ($request->input('self_eligible') == 'on') ? 1 : 0,
                'subcontracting_details' => $request->input('subcontracting_details'),
                'organization_id' => Auth::user()->organization->id,
                'tender_id' => $entity->tender->id,
                'payment_amount' => $paymentAmount,
                'status' => 'draft'
            ]);
            $entity->bids()->save($bid);
            $bid->values()->sync(collect($request->input('values'))->flatten()->toArray());
            $this->_uploadDocs($bid, $request, "/bids/{$bid->id}/");

            //TODO
            //Вернуть асинхронную подачу, сделав контроль над публикацией
            //Event::fire(new BidSaveEvent($bid));

            $structure = new \App\Api\Struct\Bid($bid);
            $api = new Api();
            $response = $api->post($structure);

            if ($api->responseCode == 201) {
                if ($entity->tender->procedureType->procurement_method_type == 'aboveThresholdEU') {
                    $bid->status = 'pending';
                } else {
                    $bid->status = 'active';
                }
                if (isset($response['data']['id'])) {
                    $bid->cbd_id = $response['data']['id'];
                    if (isset($response['access']['token'])) {
                        $bid->access_token = $response['access']['token'];
                    }
                    $bid->save();
                }
                foreach ($bid->documents as $document) {
                    if (empty($document->url)) {
                        Event::fire(new BidDocUploadEvent($document));
                    }
                }

                $structure = new \App\Api\Struct\Bid($bid);
                $response = $api->patch($structure);
                if ($api->responseCode == 200) {
                    if (env('APP_ENV' == 'server' && $entity->tender->mode == 1)) {
                        Mail::queue('emails.admin.publish-failed', ['id' => $bid->id, 'data' => $response], function ($message) {
                            $message->to('azarov.andreas@gmail.com')->subject('Предложение опубликовано успешно ('.env('APP_ENV').') ');
                            $message->to('spasibova@zakupki.com.ua')->subject('Предложение опубликовано успешно ('.env('APP_ENV').') ');
                        });
                    }
                }
            } elseif (isset($response['status']) && $response['status'] == 'error') {
                foreach ($response['errors'] as $error) {
                    if (env('APP_ENV' == 'server')) {
                        Mail::queue('emails.admin.publish-failed', ['time' => date('Y-m-d H:i:s'), 'user' => Auth::user()->id, 'id' => $bid->id,
                            'error' => $error, 'data' => $structure->getData()], function ($message) {
                            $message->to('azarov.andreas@gmail.com')->subject('Ошибка публикации предложения (' . env('APP_ENV') . ') ');
                            $message->to('spasibova@zakupki.com.ua')->subject('Ошибка публикации предложения (' . env('APP_ENV') . ') ');
                        });
                    }
                    throw new \Exception(json_encode($response['errors']));
                }
            } else {
                throw new \Exception();
            }

            $bid->amountHistory()->save(History::create([
                'alias' => 'bid.amount',
                'field_name' => 'amount',
                'field_value' => $bid->amount,
            ]));

            foreach($bid->values as $bidValue) {
                $bid->featureValueHistory()->save(History::create([
                    'alias'       => 'feature.value',
                    'field_name'  => 'title',
                    'field_value' => $bidValue->title,
                ]));
            }

            $newBalance = Payments::balance()->amount;
            if ($entity->tender->mode == 1) {
                Session::flash('flash_message', 'Пропозицію відправлено. З Вашого балансу була знята сума '.$paymentAmount.' UAH Ваш поточний баланс складає '.$newBalance.' UAH');
            } else {
                Session::flash('flash_message', 'Пропозицію відправлено.');
            }

            if ($entity->auction_start_date || $entity->tender->auction_start_date) {
                $auctionDate = ($entity->auction_start_date) ? $entity->auction_start_date : $entity->tender->auction_start_date;
                $notification_service = new NotificationService();
                $tags = new Tags();
                $tags->set_tender_link('<a href="'.URL::route('tender.show', [$entity->tender->id]).'">'.$entity->tender->tenderID.'</a>');
                $tags->set_offers_link('<a href="'.URL::route('bid.list').'">Мої пропозиції</a>');
                $tags->set_tender_date($auctionDate);
                $notification_service->create($tags, NotificationTemplate::TENDER_SET_DATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
            }
            $entity->tender->priority = 1;
            $entity->tender->save();

            DB::commit();
            $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('flash_modal', '<span style="color:red">При подачі пропозиції виникла помилка. Будь-ласка, зверніться до адміністрації.</span>');
            Mail::queue('emails.admin.publish-failed', ['time' => date('Y-m-d H:i:s'), 'user' => Auth::user()->id, 'id' => $bid->id,
                'error' => $e->getMessage().'<br>'.$e->getFile().'<br>'.$e->getLine(), 'data' => $structure->getData()], function ($message) {
                $message->to('azarov.andreas@gmail.com')->subject('Ошибка публикации предложения ('.env('APP_ENV').') ');
                $message->to('spasibova@zakupki.com.ua')->subject('Ошибка публикации предложения ('.env('APP_ENV').') ');
                $message->to('manager@zakupki.com.ua')->subject('Ошибка публикации предложения ('.env('APP_ENV').') ');
                $message->to('dex.maks@mail.ru')->subject('Ошибка публикации предложения ('.env('APP_ENV').') ');
            });

            return redirect()->back();
        }

        return redirect()->route('bid.list');
    }

    public function timeValidation($tender){
        $endDate = Carbon::parse($tender->tender_end_date);
        $datNaw = Carbon::parse(date('d.m.Y H:i'));
        $type = Auth::user()->organization->type;
        if ($type != 'supplier'){
            Session::flash('flash_message', 'Шановний користувач, ваша роль не дозволяе Вам приймати участь у тенедрі.');
            return false;
        }

        if ($datNaw->gt($endDate)){
            Session::flash('flash_message', 'Шановний користувач, період подачі пропозицій завершився, тому подача пропозиції вже неможлива.');
            return false;
        }else{
            return true;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if ($user->super_user) {
            $bid = Bid::find($id);
        } else {
            $bid = $user->organization->bids()->with('values')->findOrFail($id);
            $bidHistory = $user->organization->bids()->findOrFail($id)->amountHistory;
        }
        $documentTypes = DocumentType::whereNamespace('bid')->get();
        $entity = $bid->bidable;

        $history = $bid->amountHistory;
        $featureHistory = $bid->featureValueHistory;

        $allValues = [];

        $features = Feature::where('tender_id', $bid->tender->id)->get();

        foreach($features as $feature) {
            foreach($feature->values as $value) {
                $allValues[$value->id] = $value->value;
            }
        }
        return view('pages.bids.show', [
            'bid'            => $bid,
            'entity'         => $entity,
            'features'       => collect($features)->sort(),
            'allValues'      => collect($allValues),
            'documentTypes'  => $documentTypes,
            'history'        => $history,
            'featureHistory' => $featureHistory
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        if ($user->super_user) {
            $bid = Bid::find($id);
        } else {
            $bid = $user->organization->bids()->with('values')->findOrFail($id);
            $bidHistory = $user->organization->bids()->findOrFail($id)->amountHistory;
        }
        $documentTypes = DocumentType::whereNamespace('bid')->get();
        $entity = $bid->bidable;
        if ($entity->tender->mode == 1 && Auth::user()->organization->mode == 0) {
            \Session::flash('flash_modal', "                        <h2>Шановний Користувач.</h2>
                <p>Для використання електронного майданчика \"Zakupki UA\" з метою участі у якості Постачальника в закупівлях у відповідності до законодавства у сфері публічних закупівель, кожен Користувач, крім реєстрації/авторизації на електронному майданчику \"Zakupki UA\", має:
<ul><li>укласти відповідний договір з ТОВАРИСТВОМ З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ \"ЗАКУПІВЛІ ЮА\", яке є Оператором електронного майданчика \"Zakupki UA\" (Оператор);</li>
<li>здійснити оплату з власного поточного (розрахункового) рахунку за наданим Оператором рахунком (Авторизація користувача);</li>
<li>пройти ідентифікацію та отримати від Оператора доступ до електронної системи закупівель.</li></ul></p>
<p>Після реєстрації на електронному майданчику \"Zakupki UA\" на e-mail Користувача направляється проект договору, відповідно до якого Користувачу надаватимуться послуги.</p>
                <p>Користувачі мають змогу ознайомитись з чинними редакціями Регламенту електронного майданчика \"Zakupki UA\" і Тарифів електронного майданчика \"Zakupki UA\" за посиланнями: <a href=\"https://lp.zakupki.com.ua/reglament\">https://lp.zakupki.com.ua/reglament</a>  і <a href=\"https://lp.zakupki.com.ua/pricing\">https://lp.zakupki.com.ua/pricing</a>.</p>
                <br>
                <p>З повагою,<br>
                Служба підтримки Zakupki UA<br>
                support@zakupki.com.ua</p>");

            return redirect()->route('tender.show', [$entity->tender->id]);
        }

        $history = $bid->amountHistory;
        $featureHistory = $bid->featureValueHistory;

        $allValues = [];


        $features = Feature::where('tender_id', $bid->tender->id)->get();

        foreach($features as $feature) {
            foreach($feature->values as $value) {
                $allValues[$value->id] = $value->value;
            }
        }
        return view('pages.bids.edit', [
            'bid'            => $bid,
            'entity'         => $entity,
            'features'       => collect($features)->sort(),
            'allValues'      => collect($allValues),
            'documentTypes'  => $documentTypes,
            'history'        => $history,
            'featureHistory' => $featureHistory
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

        $user = Auth::user();
        if ($user->super_user) {
            $bid = Bid::find($id);
        } else {
            $bid = $user->organization->bids()->findOrFail($id);
        }

        $bid = Auth::user()->organization->bids()->findOrFail($id);
        $rules = [
            'amount' => 'required',
            'less' => 'in:1'
        ];
        $requestData = $request->all();
        if ($requestData['amount'] <= $bid->bidable->amount) {
            $requestData['less'] = 1;
        } else {
            $requestData['less'] = 0;
        }
        $messages = [
            'amount.required' => 'Вкажіть суму пропозиції',
            'less.in' => "Сума пропозиції не повинна перевищувати очікувану вартість ".($bid->bidable->type == 'tender' ? 'закупвілі' : 'лоту'),
        ];

        if (isset($requestData['bid']['confidential']) && is_array($requestData['bid']['confidential'])) {
            foreach ($requestData['bid']['confidential'] as $index => $confidential) {
                if ($confidential && isset($requestData['bid']['confidentialCause']) && strlen($requestData['bid']['confidentialCause'][$index]) < 30) {
                    $rules['confidentialCause[]'] = 'required';
                    $messages['confidentialCause[].required'] = 'Мінімальна довжина причини конфіденційності - 30 символів.';
                }
            }
        }

        $validator = Validator::make($requestData, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('bid.edit', [$id])
                ->withErrors($validator)
                ->withInput();
        }

        $oldBid = $bid->amount;

        $bid->amount = $request->input('amount');
        $bid->signed = 0;
        $bid->subcontracting_details = $request->input('subcontracting_details');
        if ($bid->status == 'invalid') {
            if ($bid->tender->procedureType->procurement_method == 'aboveThresholdEU') {
                $bid->status = 'pending';
            } else {
                $bid->status = 'active';
            }
        }
        $bid->save();

        if ($oldBid != $bid->amount) {

            $bid->amountHistory()->save(History::create([
                'alias' => 'bid.amount',
                'field_name' => 'amount',
                'field_value' => $bid->amount,
            ]));
        }

        $currentValuesBeforeSaving = $bid->values()->get()->lists('id')->flatten()->toArray();

        $bid->values()->sync(collect($request->input('values'))->flatten()->toArray());

        $currentAfterSaving = $bid->values()->get()->lists('id')->flatten()->toArray();

        /**
         * Если фичерсы были изменены то нужно записать в историю
         */
        if($currentValuesBeforeSaving != $currentAfterSaving) {
            $idsForInseringToHistory = array_diff($currentAfterSaving,$currentValuesBeforeSaving);
            foreach($idsForInseringToHistory as $id){
                $value = $bid->values()->find($id);
                $bid->featureValueHistory()->save(History::create([
                    'alias' => 'feature.value',
                    'field_name' => 'amount',
                    'field_value' => $value->title,
                ]));
            }

        }

        $this->_uploadDocs($bid, $request, "/bids/{$bid->id}/");
//        $this->_uploadDocs($bid, $request->file('newfiles'), "/bids/{$bid->id}/", true);
        Event::fire(new BidSaveEvent($bid));
        Session::flash('flash_message', 'Пропозицію оновлено');

        if ($bid->tender->procedureType->threshold_type == 'above') {
            $notification_service = new NotificationService();
            $tags = new Tags();
            $tags->set_offers_link('<a href="'.URL::route('bid.list').'">Мої пропозиції</a>');
            $notification_service->create($tags, NotificationTemplate::OFFER_UPDATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
        }

        return redirect()->route('bid.edit', $bid->id);
    }

    /**
     * Display a qualify form.
     *
     * @return Response
     */
    public function qualification($id)
    {
        $qualification = Qualification::find($id);
        $tender = $qualification->bid->tender;

        $qualificationDocumentTypes = DocumentType::whereNamespace('qualification')->lists('document_type', 'id');

        $grounds = '';
        $groundsForRejections = GroundsForRejection::prequalification()->get();
        foreach ($groundsForRejections as $ground) {
            $grounds['titles'][$ground->id] = $ground->title;
            $grounds['descriptions'][$ground->id] = $ground->description;
        }

        return view('pages.bids.qualification', ['tender' => $tender, 'qualification' => $qualification, 'bid' => $qualification->bid, 'groundsForRejections' => $grounds, 'qualificationDocumentTypes' => $qualificationDocumentTypes]);
    }

    /**
     * Return list of qualifications
     *
     * @param Request $request
     * @param $id
     */
    public function qualifications(Request $request, $id)
    {
        if (Auth::user()->super_user) {
            $tender = Tender::find($id);
        } else {
            $tender = Auth::user()
                ->organization
                ->tenders()->findOrFail($id);
        }

        return view('pages.bids.qualifications', compact('tender'));
    }

    /**
     * qualify bid
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function qualify(Request $request, $id)
    {
        $bidData = $request->all();
        $qualification = Qualification::findOrFail($id);
        $bid = $qualification->bid;

        if ($bidData['status'] == 'cancelled') {
            Session::flash('flash_message', 'Пропозицію кваліфіковано.');

            $this->dispatch(new ChangeStatus($qualification, $bidData['status']));

            return redirect()->route('bid.qualifications', [$bid->tender_id]);
        }

        $rules = [
            'protocol' => 'required',
            'status' => 'in:active,unsuccessful',
        ];
        $messages = [
            'protocol.required' => "Додайте протокол розгляду",
            'status.in' => "Кваліфікуйте або відхиліть пропозицію",
        ];

        $qualificationDocumentTypes = DocumentType::whereNamespace('qualification')->lists('document_type', 'id');
        if ($qualification->documents->count() > 0) {
            foreach ($qualification->documents as $document) {
                $docType = $qualificationDocumentTypes[$document->type_id];
                unset($rules[$docType]);
                unset($messages[$docType . '.required']);
            }
        }

        if ($bidData['status'] == 'active') {
            $rules['qualification_self_qualified'] = 'required';
            $rules['qualification_self_eligible'] = 'required';
            $messages['qualification_self_qualified.required'] = "Підтвердіть відповідність кваліфікаційним критеріям";
            $messages['qualification_self_eligible.required'] = "Підтвердіть відсутність підстави для відмови в участі згідно ст. 17 Закону України ”Про Публічні закупівлі”";

            $validator = Validator::make($bidData, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            //$qualification->status = 'active';
            $qualification->qualified = 1;
            $qualification->eligible = 1;
            $qualification->unsuccessful_title = null;
            $qualification->unsuccessful_description = null;
        } elseif ($bidData['status'] == 'unsuccessful') {
            $rules['unsuccessful_title'] = 'required';
            $rules['unsuccessful_description'] = 'required';
            $messages['unsuccessful_title.required'] = "Виберіть причини відхилення";
            $messages['unsuccessful_description.required'] = "Введить пояснення відхилення";

            $validator = Validator::make($bidData, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            //k$qualification->status = 'unsuccessful';
            $qualification->unsuccessful_title = json_encode($bidData['unsuccessful_title']);
            $qualification->unsuccessful_description = $bidData['unsuccessful_description'];
            $qualification->qualified = 0;
            $qualification->eligible = 0;
        } else {
            $validator = Validator::make($bidData, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $qualification->save();

        $protocolTypeID = DocumentType::where('namespace', 'qualification')
            ->where('document_type', 'protocol')
            ->pluck('id');

        $path = "/qualifications/{$qualification->id}/";
        $file = (isset($bidData['protocol'])) ? $bidData['protocol'] : null;
        $fileTypeID = $protocolTypeID;
        if ($file) {
            $oldDoc = $qualification->documents->where('type_id', $fileTypeID)->first();

            Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));

            $params = [
                'path' => $path . $file->getClientOriginalName(),
                'type_id' => $fileTypeID,
                'orig_id' => '',
                'url' => '',
                'title' => '',
            ];
            if (is_object($oldDoc)) {
                $oldDoc->update($params);
            } else {
                $newDoc = new QualificationDocuments($params);
                $qualification->documents()->save($newDoc);
            }
        }

        $documents = QualificationDocuments::where('qualification_id', $qualification->id)->get();
        foreach ($documents as $document) {
            if (empty($document->url)) {
                Event::fire(new QualificationDocUploadEvent($document));
            }
        }

        Event::fire(new QualificationSaveEvent($qualification));
        Session::flash('flash_message', 'Пропозицію кваліфіковано.');

        $this->dispatch(new ChangeStatus($qualification, $bidData['status']));

        return redirect()->route('bid.qualifications', [$bid->tender_id]);
    }

    public function updateBid($id)
    {
        $user = Auth::user();
        if ($user->super_user) {
            $bid = Bid::find($id);

            $api = new Api();
            dd($api->get($bid->tender->cbd_id, 'bids/'.$bid->cbd_id, $bid->access_token));

            Event::fire(new BidSaveEvent($bid));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        $bid = Auth::user()->organization->bids()->findOrFail($id);

        $id = $bid->tender_id;
        if($id !== 0) {

            Event::fire(new BidDeleteEvent($bid));
            Session::flash('flash_message', 'Пропозиція видалена');
            return redirect()->route('bid.list');
        }
    }

    public function downloadAll($id)
    {
        $bid = Bid::find($id);
        if ($bid) {
	    	$documents = Bid::find($id)->documents;
	    	if (count($documents)>0) {
		        foreach ($documents as $document) {
		            if (!empty($document->url)) {
		            	//$content = @file_get_contents($document->url);
		            	$url = $document->url.'&acc_token='.$document->bid->access_token;
            			$api = new Api();
            			$content = $api->getRaw($url);
		            	if ($content !== FALSE) {
		                	Storage::disk('documents')->put("archive/bids/$id/".$document->title, $content);
		            	}
		            }
		        }
		        $files = glob(storage_path("app/documents/archive/bids/$id/")."*");
		        $zipper = new Zipper;
		        $zipper->zip(storage_path()."/dw/документи_пропозиції_$id.zip")->add($files)->close();
		        
		        if (file_exists(storage_path()."/dw/документи_пропозиції_$id.zip")) {
					return response()->download(storage_path()."/dw/документи_пропозиції_$id.zip");
		        } else {
		        	abort(403);
		        }
	    	} 
	    	abort(403);
        } else {
        	abort(403);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $bidDocument = BidDocuments::find($id);
        $user = $bidDocument->bid->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }
        if ($bidDocument->bid->tender->procedureType->threshold_type != 'below' && $bidDocument->bid->documents()->where('orig_id', '!=', $bidDocument->orig_id)->count() < 1)
            Session::flash('flash_error', 'У пропозиції повинен бути хоча б один документ');
        else
            $bidDocument->bid->documents()->where('orig_id', $bidDocument->orig_id)->delete();
        //BidDocuments::where('orig_id', $bidDocument->orig_id)->delete();
//        $bidDocs = BidDocuments::where('orig_id', $bidDocument->orig_id)->get();
//        foreach ($bidDocs as $bidDoc) {
//            $this->dispatch((new DeleteBidDocument($bidDoc)));
//            $bidDoc->delete();
//        }

        return redirect()->back();
    }

    public function download($id)
    {
        $document = BidDocuments::find($id);
        if (!empty($document->url)) {
            $url = $document->url.'&acc_token='.$document->bid->access_token;
            $api = new Api();
            $stream = $api->getRaw($url);

            header("Content-Type: " . $document->format);
            header("Content-Length: " . $stream->getSize());
            header('Content-disposition: attachment; filename="' . $document->title . '"');
            echo $stream->getContents();
        }
    }


    public function getBids()
    {
        if (Auth::user()->super_user){
            $date_start = (new Carbon('first day of last month'));
            $date_end = (new Carbon('first day of this month'));

            $bids =  Bid::select('tender_id','cbd_id AS ID_пропозиції','organization_id','amount AS Сумма_пропозиції','payment_amount AS Плата_за_участь')->where('access_token','!=','')->whereHas('tender',function($z) use ($date_start,$date_end){
                $z->where('mode',1);
                $z->whereHas('award',function($q) use ($date_start,$date_end){
                    $q->whereBetween('created_at',[$date_start->toDateString(), $date_end->toDateString()]);
                });
            })->get();
            $i = 0;
            foreach($bids as $bid){
                $bid['organization_name'] = $bid->organization['name'];
                $bid['cbd_tender'] = $bid->tender['cbd_id'];
                $bids[$i] = $bid;
                $i++;
            }
            $entitiesArray = $bids->toArray();
            $bidsFinal = [];
            if(count($entitiesArray) > 0) {
                foreach ($entitiesArray as $bids) {
                    unset($bids['organization']);
                    unset($bids['tender']);
                    $bidsFinal[] = $bids;
                }
            }
            Excel::create('bids_'.$date_start->format('m.Y'), function($excel) use ($bidsFinal) {
                $excel->sheet('Скарги', function($sheet) use ($bidsFinal) {
                    $sheet->fromArray($bidsFinal);
                });
            })->download('xlsx');

        }else{
            return redirect()->back();
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
        $bid = Bid::find($id);
        $sign = $bid->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();

        $api = new Api(false);
        $api->namespace = 'tenders';
        $result = $api->get($bid->tender->cbd_id, 'bids/' . $bid->cbd_id, $bid->access_token);
        $result['sign'] = false;
        if ($sign) {
            $document = BidDocuments::find($sign->id);
            if (!empty($document->url)) {
                $url = $document->url . '&acc_token=' . $document->bid->access_token;
                $api = new Api();
                $stream = $api->getRaw($url);
                $result['sign'] = $stream->getContents();
            }
        }

        return $result;
    }

    public function postSign(Request $request, $id)
    {
        $sign = $request->get('sign');
        if (self::isValidSignature($sign)) {

            if (Auth::user()->super_user) {
                $bid = Bid::find($id);
            } else {
                $bid = Auth::user()
                    ->organization
                    ->bids()->findOrFail($id);
            }

            Storage::disk('documents')->put("/bids/$id/sign.p7s", $sign);
            $document = $bid->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();
            if (!$document) {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'path' => "/bids/$id/sign.p7s",
                    'bid_id' => $bid->id,
                    'status' => 'new'
                ];

                $newDocument = new BidDocuments($params);
                $bid->documents()->save($newDocument);
            } else {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'orig_id' => $document->orig_id,
                    'path' => "/bids/$id/sign.p7s",
                    'bid_id' => $bid->id,
                    'status' => 'new'
                ];
                $document->status = 'old';
                $document->save();

                $existingDocument = new BidDocuments($params);
                $bid->documents()->save($existingDocument);
            }
            $bid->signed = 1;
            $bid->save();

            Event::fire(new BidSaveEvent($bid));

            return ['result' => 'success', 'message' => ''];
        }

        return ['result' => 'failed', 'message' => 'Підпис не переданий'];
    }

}
