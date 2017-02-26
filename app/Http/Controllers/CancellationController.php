<?php

namespace App\Http\Controllers;

use App\Events\CancelActivateEvent;
use App\Events\CancelSaveEvent;
use App\Http\Requests;
use App\Model\Cancellation;
use App\Model\CancellationDocuments;
use App\Model\Lot;
use Event;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CancellationController extends Controller
{
    protected function _uploadDocs($cancel, $files, $path) {
        foreach ($files as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                $params = [
                    'path'        => $path . $file->getClientOriginalName(),
                ];

                $newDoc = new CancellationDocuments($params);

                $cancel->documents()->save($newDoc);
            }
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($entityName, $id)
    {
        if ($entityName == 'tender') {
            $tender = $entity = Auth::user()->organization->tenders()->findOrFail($id);
        } else {
            $entity = Lot::with('tender')->findOrFail($id);
            $tender = $entity->tender;
            if ($entity->tender->organization->id != Auth::user()->organization->id) {

                return abort(403);
            }
        }

        return view('pages.cancellation.create', ['entity' => $entity, 'tender' => $tender]);
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
            $entity = Auth::user()->organization->tenders()->findOrFail($entityId);
        } else {
            $entity = Lot::with('tender')->findOrFail($entityId);
            if ($entity->tender->organization->id != Auth::user()->organization->id) {

                return abort(403);
            }
        }
        $cancellation = new Cancellation($request->all());
        $cancellation->status = 'pending';
        $entity->cancel()->save($cancellation);

        $this->_uploadDocs($cancellation, $request->file('cancel')['files'], "/cancel/{$cancellation->id}/");
        Event::fire(new CancelSaveEvent($cancellation));

        return redirect()->route('cancellation.edit', [$cancellation->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $cancel = Cancellation::find($id);
        $tender = $cancel->tender;
        if (!$tender->isOwner() && $cancel->status != 'active') {
            abort(403);
        }

        return view('pages.cancellation.detail', ['cancel' => $cancel, 'tender' => $tender]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $cancel = Cancellation::find($id);
      //  $user = $cancel->tender->organization->user()->find(Auth::user()->id);
        $tender = $cancel->tender;
        if (!$tender->isOwner()) {
            abort(403);
        }

        return view('pages.cancellation.edit', ['cancel' => $cancel, 'tender' => $tender]);
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
        $cancellation = Cancellation::find($id);
        $user = $cancellation->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        $cancellation->save($request->all());
        $this->_uploadDocs($cancellation, $request->file('cancel')['files'], "/cancel/{$id}/");

        Session::flash('flash_message', 'Заявка сохранена.');
        Event::fire(new CancelSaveEvent($cancellation));

        return redirect()->route('cancellation.edit', [$id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $cancelDoc = CancellationDocuments::find($id);
        $user = $cancelDoc->cancel->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        $cancelDoc->delete();

        return redirect()->back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id)
    {
        $cancel = Cancellation::find($id);
        $user = $cancel->tender->organization->user()->find(Auth::user()->id);
        if (!$user) {
            abort(403);
        }

        if($cancel->tender->isOpen() && (!$cancel->tender->canCancelAfterComplaintEndDate() || $cancel->tender->hasPendingComplaints())) {
            Session::flash('flash_error', 'Закупівля не може бути скасована допоки є хоч одна скарга або не сплине період оскарження закупівлі');
            return redirect()->back();
        }

        $cancel->status = 'active';
        $cancel->save();
        Event::fire(new CancelActivateEvent($cancel));

        Session::flash('flash_message', 'Заявка активована.');

        return redirect()->route('home');
    }
}
