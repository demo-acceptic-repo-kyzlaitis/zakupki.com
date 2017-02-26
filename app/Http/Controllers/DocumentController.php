<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Model\Document;
use App\Model\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @param $organizationWrongId
     * @param $tenderWrongId
     *
     * @return Response
     * @internal param Request $request
     */
    public function index($organizationWrongId, $tenderWrongId) {
        $organizationIds = Session::get('organizationIds');
        $tenderIds = Session::get('tenderIds');

        $documents = Auth::user()
            ->organizations->find($organizationIds[$organizationWrongId])
            ->tenders->find($tenderIds[$tenderWrongId])
            ->documents;

        return view('pages.document.show-document-lists', compact('documents', 'organizationWrongId', 'tenderWrongId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $organizationWrongId
     * @param $tenderWrongId
     *
     * @return Response
     */
    public function create($organizationWrongId, $tenderWrongId) {
        $documentTypes = DocumentType::all();
        $organizationIds = Session::get('organizationIds');
        $tenderIds = Session::get('tenderIds');

        $tender = Auth::user()->organizations
            ->find($organizationIds[$organizationWrongId])
            ->tenders->find($tenderIds[$tenderWrongId]);

        return view('pages.document.add-document-form', compact('tender', 'organizationWrongId', 'tenderWrongId', 'documentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @param          $organizationWrongId
     * @param          $tenderWrongId
     *
     * @return Response
     */
    public function store(Request $request, $organizationWrongId, $tenderWrongId) {

        $organizationIds = Session::get('organizationIds');
        $tenderIds = Session::get('tenderIds');

        $storagePath = Config::get('filesystems.disks.documents.root');
        $tender = Auth::user()
            ->organizations->find($organizationIds[$organizationWrongId])
            ->tenders->find($tenderIds[$tenderWrongId]);

        $path = "/organization/{$organizationIds[$organizationWrongId]}/tender/{$tenderIds[$tenderWrongId]}/";
        $docTypes = $request->get('docTypes');
        $files = $request->file('files');

        foreach ($files as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));

                $newDoc = new Document([
                    'title'       => '',
                    'description' => '',
                    'format'      => $file->getClientMimeType(),
                    'url'         => $storagePath . $path . $file->getClientOriginalName(),
                ]);

                $tender->documents()->save($newDoc);
                $newDoc->documentTypes()->attach($docTypes[$index]);
            }
        }

        return redirect()->route('organization.tender.document.index', [$organizationWrongId, $tenderWrongId]);
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
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id) {
        //
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

    public function download($id, $entity = '')
    {
        if (empty($entity) || $entity == 'complaint' || $entity == 'plan')
            $class = "App\\Model\\" . ucfirst($entity) . "Document";
        else
            $class = "App\\Model\\" . ucfirst($entity) . "Documents";

        $document = $class::find($id);
        return response()->download(storage_path('app/documents') . $document->path);
    }
}
