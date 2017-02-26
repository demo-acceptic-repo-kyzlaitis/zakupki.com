<?php

namespace App\Http\Controllers;

use App\Events\ComplaintDocUploadEvent;
use App\Events\ComplaintSaveEvent;
use App\Http\Requests;
use App\Model\Award;
use App\Model\Complaint;
use App\Model\ComplaintDocument;
use App\Model\Lot;
use App\Model\Qualification;
use App\Model\Tender;
use Carbon\Carbon;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    protected function _uploadDocs($entity, $files, $path, $isNew = false)
    {
        if (is_array($files)) foreach ($files as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                $params = [
                    'document_parent_id' => $isNew ? $index : 0,
                    'title' => '',
                    'description' => '',
                    'format' => '',
                    'path' => $path . $file->getClientOriginalName(),
                    'organization_id' => Auth::user()->organization->id
                ];

                if ($entity->organization_id == Auth::user()->organization->id) {
                    $params['author'] = 'complaint_owner';
                } else {
                    $params['author'] = 'tender';
                }


                if ($isNew) {
                    $oldDoc = ComplaintDocument::find($index);
                    $params['type_id'] = $oldDoc->type_id;
                    $params['orig_id'] = $oldDoc->orig_id;
                } else {
                    $params['type_id'] = 0;
                }

                $newDoc = new ComplaintDocument($params);

                $entity->documents()->save($newDoc);
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($entityName, $id)
    {
        $tender = $entity = Tender::findOrFail($id);
        $complaints = Complaint::where('status', '!=', 'draft')->where('tender_id', $id)->get();
        $userType = Auth::user()->organization->type;

        return view('pages.complaint.list', ['entity' => $entity, 'tender' => $tender, 'userType' => $userType, 'complaints' => $complaints]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing()
    {
        if (Auth::user()->organization->type == 'customer') {
            $complaints = Complaint::where('status', '!=', 'draft')->where('tender_organization_id', Auth::user()->organization->id)->orderBy('updated_at', 'desc')->paginate(20);
        } else {
            $complaints = Complaint::where('organization_id', Auth::user()->organization->id)->orderBy('updated_at', 'desc')->paginate(20);
        }
        return view('pages.complaint.listing', ['complaints' => $complaints]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($entityName, $id)
    {

        if ($entityName == 'tender') {
            $entity = $tender = Tender::findOrFail($id);
        } elseif ($entityName == 'qualification') {
            $entity = Qualification::findOrFail($id);
            $tender = $entity->bid->tender;
        } elseif ($entityName == 'lot') {
            $entity = Lot::findOrFail($id);
            $tender = $entity->tender;
        } else {
            $entity = Award::findOrFail($id);
            $tender = $entity->tender;
        }
        return view('pages.complaint.create', ['entity' => $entity, 'tender' => $tender, 'entity_type' => $entityName]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $entityName = $request->input('entity_type');
        $entityId = $request->input('entity_id');
        if ($entityName == 'tender') {
            $entity = $tender = Tender::findOrFail($entityId);
        } elseif ($entityName == 'qualification') {
            $entity = Qualification::findOrFail($entityId);
            $tender = $entity->bid->tender;
        } elseif ($entityName == 'lot') {
            $entity = Lot::findOrFail($entityId);
            $tender = $entity->tender;
        } else {
            $entity = Award::findOrFail($entityId);
            $tender = $entity->tender;
        }
        if($entity->canComplaint() == false){
            Session::flash('flash_error', 'Час подачі вимог або скарг завершено');
            return redirect()->back();
        }
        $data = $request->all();
        $data['organization_id'] = Auth::user()->organization->id;
        $data['tender_organization_id'] = $tender->organization->id;
        $data['tender_id'] = $tender->id;
        $data['status'] = 'draft';
        $data['type'] = 'claim';
        $complaint = new Complaint($data);
        $entity->complaints()->save($complaint);
        $this->_uploadDocs($complaint, $request->file('complaint')['files'], "/complaint/{$complaint->id}/");
        Event::fire(new ComplaintSaveEvent($complaint, Auth::user()->organization));

        return redirect()->route('complaint.edit', [$complaint->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $complaint = Complaint::findOrFail($id);
        if ($complaint->complaintable->type == 'qualification') {
            $tender = $complaint->complaintable->bid->tender;
        } else {
            $tender = $complaint->complaintable->tender;
        }

        return view('pages.complaint.detail', ['complaint' => $complaint, 'tender' => $tender]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $complaint = Complaint::where('id', $id)->where(function ($query) {
            $query->orWhere('organization_id', Auth::user()->organization->id)
                ->orWhere('tender_organization_id', Auth::user()->organization->id);
        })->first();
//        if ($complaint->complaintable_type == "App\\Model\\Qualification") {
//            $tender = $complaint->complaintable->bid->tender;
//        } else {
        $tender = $complaint->complaintable->tender;
//        }
        $entity = $complaint->complaintable;

        if (!$complaint) {
            abort(403);
        }
        
        return view('pages.complaint.edit', ['complaint' => $complaint, 'tender' => $tender, 'entity' => $entity]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\UpdateComplaintRequest $request, $id)
    {
        $complaint = Complaint::where('id', $id)->where(function ($query) {
            $query->where('organization_id', Auth::user()->organization->id)
                ->orWhere('tender_organization_id', Auth::user()->organization->id);
        })->first();

        if (!$complaint) {
            abort(403);
        }

        //entity может быть lot, tender, award
        $entity = $complaint->complaintable;


        //!($complaint->status == 'satisfied' && $complaint->type == 'complaint') для дозагрузки доков по требованию амку
        if($entity->canComplaint() == false && !($complaint->status == 'satisfied' && $complaint->type == 'complaint')){
            Session::flash('flash_error', 'Час подачі вимог або скарг завершено');
            return redirect()->back();
        }

        $data = $request->all();
        if (isset($data['resolution_type'])) {
            $data['status'] = 'answered';
            $data['date_answered'] = Carbon::now();
        }
        if (isset($data['satisfied'])) {
            if ($data['satisfied'] == 1) {
                $data['status'] = 'resolved';
            } else {
                $data['status'] = 'pending';
                $data['type'] = 'complaint';
                $data['date_escalated'] = Carbon::now();
            }
        }

        $complaint->update($data);
        $this->_uploadDocs($complaint, $request->file('complaint')['files'], "/complaint/{$id}/");

        /** Если был дан ответ заказчика, то сначала загружаем документы ответа путем фаера события и потом только событие об изменении жалбы */
        if (isset($data['resolution_type'])) {
            foreach ($complaint->documents as $document) {
                if ($document->url == '') {
                    Event::fire(new ComplaintDocUploadEvent($document));
                }
            }
        }

        Session::flash('flash_message', 'Дані збережено');
        Event::fire(new ComplaintSaveEvent($complaint, Auth::user()->organization));

        return redirect()->route('complaint.edit', [$id]);
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

    public function claim($id)
    {
        $complaint = Auth::user()->organization->complaints()->findOrFail($id);
        if($complaint->canComplaint() == false){
            Session::flash('flash_error', 'Час подачі вимог завершено');
            return redirect()->back();
        }
        if($complaint->access_token == ''){
            Session::flash('flash_error', 'Чернетка знаходиться на публікації. Будь ласка, спробуйте пізніше.');
            return redirect()->back();
        }
        $complaint->status = 'claim';
        $complaint->save();

        Session::flash('flash_message', 'Вимога подана.');
        Event::fire(new ComplaintSaveEvent($complaint, Auth::user()->organization));

        return redirect()->back();
    }

    public function complaint($id)
    {
        $complaint = Auth::user()->organization->complaints()->findOrFail($id);
        if($complaint->canComplaint() == false){
            Session::flash('flash_error', 'Час подачі жалоб завершено');
            return redirect()->back();
        }
        if($complaint->access_token == ''){
            Session::flash('flash_error', 'Чернетка знаходиться на публікації. Будь ласка, спробуйте пізніше.');
            return redirect()->back();
        }
        $complaint->type = 'complaint';
        $complaint->status = 'pending';
        $complaint->save();

        Session::flash('flash_message', 'Cкарга подана.');
        Event::fire(new ComplaintSaveEvent($complaint, Auth::user()->organization));

        return redirect()->back();
    }

    /**
     * set complaint status to cancelled:
     * @param Request $request
     */
    public function cancelComplaint(Request $request)
    {
        $complaint = Complaint::where('id', $request->input('complaintId'))->firstOrFail();

        if ($complaint->type == 'claim') {
            $complaint->status = 'cancelled';
        } else {
            $complaint->status = 'stopping';
        }
        $complaint->cancellation_reason = $request->input('cancellation_reason');


        $complaint->save();

        Event::fire(new ComplaintSaveEvent($complaint, $complaint->organization));

        return redirect()->back();

    }
}
