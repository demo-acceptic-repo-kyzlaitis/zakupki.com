<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Model\Complaint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SoapBox\Formatter\Formatter;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $form = [];
        return view('admin.pages.complaint.list', compact('form'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request)
    {
        $form = $request->get('form');
        $complaints = Complaint::where('type','complaint')->with('documents');

        if (!empty($form['start_date']) && !empty($form['end_date'])) {
            $date_start = Carbon::parse($form['start_date'])->toIso8601String();
            $date_end = Carbon::parse($form['end_date'])->toIso8601String();
            $complaints = $complaints->whereBetween('created_at',[$date_start, $date_end]);
        }

        $complaints = $complaints->get();

        $result = [];

        foreach ($complaints as $complaint) {
            if ($complaint->complaintable) {
                $values = [
                    'Заголовок' => $complaint->title,
                    'Дата прийняття рішення' => $complaint->date_dicision,
                    'Скарга на' => $complaint->complaintable->type,
                    'Опис запитання' => $complaint->description,
                    'Дата дії учасника' => $complaint->date_action,
                    'Організація яка подає скаргу' => $complaint->organization ? $complaint->organization->name : '',
                    'Код' => $complaint->organization ? $complaint->organization->identifier : '',
                    'Телефон' => $complaint->organization ? $complaint->organization->contact_phone : '',
                    'Email' => $complaint->organization ? $complaint->organization->contact_email : '',
                    'Статус скарги' => $complaint->statusDesc->description,
                    'Документи' => '',
                    'Організація переможця' => '',
                    'Код переможця' => '',
                    'Телефон переможця' => '',
                    'Email переможця' => '',
                    'Опис Рішення' => $complaint->resolution,
                    'Дата рішення' => $complaint->date_decision
                ];
                if ($complaint->complaintable->type == 'award' && isset($complaint->complaintable->organization)) {
                    $values['Організація переможця'] = $complaint->complaintable->organization->name;
                    $values['Код'] = $complaint->complaintable->organization->name;
                    $values['Телефон'] = $complaint->complaintable->organization->name;
                    $values['Email'] = $complaint->complaintable->organization->name;
                }

                foreach ($complaint->documents as $document) {
                    $values['Документи'] .= $document->url."\n";
                }

                $result[] = $values;

            }

        }
        Excel::create('export_complaints', function($excel) use ($result) {
            $excel->sheet('Скарги', function($sheet) use ($result) {
                $sheet->fromArray($result);
            });
        })->download('xlsx');

    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
}
