<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Events\ContractDocUploadEvent;
use App\Events\ContractSaveEvent;
use App\Http\Requests;
use App\Jobs\ActivateContract;
use App\Jobs\GetContractCredentials;
use App\Model\Contract;
use App\Model\ContractChange;
use App\Model\ContractDocuments;
use App\Model\Notification;
use App\Model\RationaleType;
use App\Model\Tender;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Carbon\Carbon;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Validator;

class ContractController extends Controller
{

    const DEFAULT_LANGUAGE = 'ua';

    protected function _uploadDocs($contract, $files, $path, $isNew = false, $changeID = 0)
    {
        $changeID = (int)($changeID);
        if (is_array($files)) {
            foreach ($files as $index => $file) {
                if ($file) {
                    Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                    $params = [
                        'path' => $path . $file->getClientOriginalName(),
                        'change_id' => $changeID,
                        'document_of' => ($changeID > 0) ? 'change' : 'contract',
                    ];

                    if ($isNew) {
                        $oldDoc = ContractDocuments::find($index);
                        $params['type_id'] = $oldDoc->type_id;
                        $params['orig_id'] = $oldDoc->orig_id;
                        $params['url'] = '';
                        $params['title'] = '';
                        $oldDoc->status = 'old';
                        $oldDoc->update();
                    } else {
                        $newDoc = new ContractDocuments($params);
                        $contract->documents()->save($newDoc);
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
    public function index($tenderId)
    {
        $tender = Tender::find($tenderId);

        return view('pages.contract.list', ['tender' => $tender]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id)
    {
        $tender = Auth::user()->organization->tenders()->findOrFail($id);

        return view('pages.contract.create', ['tender' => $tender]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $tender = $request->user()->organization->tenders()->findOrFail($request->get('tender_id'));
        if ($tender->award[0]) {
            $contract = new Contract([
                'title' => 'Контракт',
                'award_id' => $tender->award[0]->id,
                'tender_id' => $tender->id,
                'description' => $request->get('description'),
                'status' => 'pending'
            ]);
            $contract->save();
            $this->_uploadDocs($contract, $request->file('files'), "/contract/{$contract->id}/");
        }


        return redirect()->route('tender.show', [$request->get('tender_id')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {

        $contract = Contract::find($id);
        if (Auth::user()->organization->id != $contract->tender->organization->id) {

        }

        $tender = $contract->tender;

        //костыль мотыль
        $this->_getMissingInfo($tender, $contract);

        return view('pages.contract.detail', ['contract' => $contract, 'tender' => $tender]);
    }
    
    public function change($id)
    {
        $contract = Contract::with('change')->find($id);
        $types = RationaleType::lists('title', 'id');

        $tender = $contract->tender;

        return view('pages.contract.change-create', compact('tender', 'contract', 'change', 'types'));
    }

    public function terminate(Request $request, $id)
    {
        $contract = Contract::find($id);
        
        $tender = $contract->tender;
        $terminateType = $request->get('type');

        return view('pages.contract.terminate', compact('tender', 'contract', 'terminateType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $tender = Auth::user()->organization->tenders->find($id);
        if (!$tender) {
            abort(403);
        }

        $contract = $tender->contract;

        return view('pages.contract.edit', ['contract' => $contract, 'tender' => $tender]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $contract = Contract::find($id);

        $data = $request->all();

        $rules = [
            'contract_number' => 'required',
            'date_signed' => 'required|date',
            'period_date_start' => 'required|date',
            'period_date_end' => 'required|date',
        ];
        $messages = [
            'contract_number.required' => 'Поле "Номер договору" повинно бути заповнене',
            'date_signed.required' => 'Поле "Дата підписання" повинно бути заповнене',
            'date_signed.date' => 'Поле "Дата підписання" повинно бути датою',
            'period_date_start.required' => 'Поле "Строк дії договору з" повинно бути заповнене',
            'period_date_start.date' => 'Поле "Строк дії договору з" повинно бути датою',
            'period_date_end.required' => 'Поле "Строк дії договору по" повинно бути заповнене',
            'period_date_end.date' => 'Поле "Строк дії договору по" повинно бути датою',
        ];
        if (isset($data['date_signed'])) {
            $rules['date_signed'] = 'required|date|before:' . date('Y-m-d H:i:s');

            $messages['date_signed.before'] = "Контракт не може бути підписаний майбутньою датою.";
        }

        if ($contract->documents()->count() == 0 && $contract->tender->type_id != 4) {
            $data['documentsCount'] = $this->_existDocuments($data);
            $fileExistsRule = [
                'documentsCount' => 'in:1'
            ];

            $fileExistsMessage = [
                'documentsCount.in' => 'Для підписання договору додайте файл з договором та натисніть Зберегти'
            ];

            $rules = array_merge($rules, $fileExistsRule);
            $messages = array_merge($messages, $fileExistsMessage);
        }

        if ($contract->award->complaint_date_end != null &&
            ($contract->tender->procedureType->procurement_method_type != 'reporting' &&
                $contract->tender->procedureType->procurement_method_type != 'competitiveDialogueUA' &&
                $contract->tender->procedureType->procurement_method_type != 'competitiveDialogueEU'
            )) {

            if (isset($data['date_signed'])) {
                $rules['date_signed'] .= '|after:' . $contract->award->complaint_date_end;
                $messages['date_signed.after'] = 'Дата підписання та Початок дії договору повинні бути не раніше ніж ' . $contract->award->complaint_date_end;
            }
        }


        if (isset($data['change']) && isset($data['change']['date_signed'])) {
            $rules = [];
            $rules['change.date_signed'] = 'required|date|before:' . date('Y-m-d H:i:s');
            $messages['change.date_signed.before'] = 'Зміни до конракту не можуть бути підписані майбутньою датою.';

            if (isset($data['change']['id'])) {
                $changes = $contract->changes()->where('id', '!=', $data['change']['id'])->orderBy('created_at', 'desc')->get();
            } else {
                $changes = $contract->changes()->orderBy('created_at', 'desc')->get();
            }
            if (!$changes->count()) {
                $afterDate = Carbon::parse($contract->date_signed)->format('Y-m-d H:i:s');
            } else {
                $afterDate = Carbon::parse($changes[0]->date_signed)->format('Y-m-d H:i:s');
            }
            $rules['change.date_signed'] = 'required|date|after:' . $afterDate;
            $messages['change.date_signed.after'] = 'Зміни до конракту не можуть бути підписані раніше попереднього конракту '.$afterDate;
        }

        if (!empty($rules)) {
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $contract->update($data);

        $user = $contract->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        if (isset($data['change']['id'])) {
            $change = ContractChange::find($data['change']['id']);
            $change->update($data['change']);
        } elseif (isset($data['change'])) {
            $data['change']['status'] = 'pending';
            $data['change']['tender_id'] = $contract->tender->id;

            $change = new ContractChange($data['change']);
            $contract->changes()->save($change);
        }

        $contract->update($data);
        if (isset($data['change'])) {
            $this->_uploadDocs($contract, $request->file('contract')['files'], "/contract/{$contract->id}/change/{$change->id}/", false, $change->id);
            $this->_uploadDocs($contract, $request->file('newfiles'), "/contract/{$contract->id}/change/{$change->id}/", true, $change->id);
        } else {
            $this->_uploadDocs($contract, $request->file('contract')['files'], "/contract/{$contract->id}/");
            $this->_uploadDocs($contract, $request->file('newfiles'), "/contract/{$contract->id}/", true);
        }
        Event::fire(new ContractSaveEvent($contract));
        Session::flash('flash_message', 'Дані контракту збережні');
        return redirect()->route('tender.contracts', [$contract->tender->id]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */

    public function activate($id)
    {
        //test string

        $contract = Contract::find($id);
        $user = $contract->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        if(count($contract->tender->allComplaints()->unsatisfiedComplaints()->get()) > 0) {
            Session::flash('flash_error', 'Процедура заблокована через наявність скарг.');
            return redirect()->back();
        }

        if (empty($contract->contract_number) || empty($contract->date_signed) || $contract->documents()->count() == 0) {
            Session::flash('flash_message', 'Будь-ласка, заповніть дані договору.');
        }

        $this->dispatch(new ActivateContract($contract));

        if ($contract->change) {
            $contract->change->status = 'activate';
            $contract->change->save();
            Session::flash('flash_message', 'Заявка на зміну до договору подана до ЦБД.');
        }else{
            Session::flash('flash_message', 'Заявка на підписання договору подана до ЦБД.');
        }
        $contract->signed = 0;
        $contract->save();
        Event::fire(new ContractSaveEvent($contract));

        $notification_service = new NotificationService();
        $tags = new Tags();
        $tags->set_offers_link('<a href="'.URL::route('bid.list').'">Мої пропозиції</a>');
        $notification_service->create($tags, NotificationTemplate::CONTRACT_ACTIVATE, $user->id, self::DEFAULT_LANGUAGE);
        return redirect()->route('tender.contracts', [$contract->tender->id]);
    }


    public function terminated($id)
    {
        //test string
        $contract = Contract::find($id);
        $user = $contract->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        $contract->status = 'terminated';
        $contract->save();
        
        Event::fire(new ContractSaveEvent($contract));
        Session::flash('flash_message', 'Заявка на завершення договору подана до ЦБД.');

        return redirect()->route('tender.contracts', [$contract->tender->id]);

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

    public function deleteDoc($id)
    {
        $contractDoc = ContractDocuments::find($id);
        $user = $contractDoc->contract->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
//            abort(403);
        }

        $contractDoc->delete();

        return redirect()->back();
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
        $contract = Contract::find($id);
        $sign = $contract->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();

        $api = new Api(false);
        $result = $api->get($contract->tender->cbd_id, 'contracts/'.$contract->cbd_id);
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

            $contract = Contract::find($id);

            Storage::disk('documents')->put("/contract/$id/sign.p7s", $sign);
            //$document = $contract->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();
            //if (!$document) {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'path' => "/contract/$id/sign.p7s",
                    'contract_id' => $contract->id
                ];

                $newDocument = new ContractDocuments($params);
                $contract->documents()->save($newDocument);
                $contract->signed = 1;
                $contract->save();
                Event::fire(new ContractDocUploadEvent($newDocument));

            /*} else {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'orig_id' => $document->orig_id,
                    'path' => "/contract/$id/sign.p7s",
                    'contract_id' => $contract->id
                ];

                $existingDocument = new ContractDocuments($params);
                $contract->documents()->save($existingDocument);
                Event::fire(new ContractDocUploadEvent($existingDocument));

            }*/
            return ['result' => 'success', 'message' => ''];
        }

        return ['result' => 'failed', 'message' => 'Підпис не переданий'];
    }

    /**
     * return 1 if exist at least 1 document and 0 if documents doesn`t exist
     *
     * @param $awardData
     * @return bool|int
     */
    protected function _existDocuments($contractData)
    {
        $filesCounter = 0;
        foreach ($contractData['contract'] as $key=>$value){
            if ($key = 'files') {
                foreach ($value as $files){
                    $filesCounter += count($files);
                }
            }
        }
        if ($filesCounter >1) return 1;
        return 0;
    }

    /**
     * @param $tender
     * @param $contract
     * @param $awardData
     */
    private function _getMissingInfo($tender, $contract) {
        if($tender->type_id == 4 && $tender->cbd_id && empty($contract->award->organization)) { // звит про укладений
            $api      = new Api();
            $supplier = $api->get($tender->cbd_id)['data']['awards'][0];

            $organization = \App\Import\Organization::getModel($supplier['suppliers'][0]);

            if($organization) {
                $awardData['organization_id'] = $organization->id;
                $contract->award->update($awardData);
            }
        }
    }
}
