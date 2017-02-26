<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Model\ReloginHistory;
use Illuminate\Http\Request;

class ReloginHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $form = $request->get('form');
        $pag = $request->get('pag');
        $sortOrder = $request->get('so');

        if($form == null){
            $form['nominal_user'] = '';
            $form['to_user'] = '';
        }
        $reloginHistory = ReloginHistory::where('id','!=', '0');
        if (!empty($form['nominal_user'])){
            $reloginHistory = $reloginHistory->where('nominal_user',$form['nominal_user']);
        }
        if (!empty($form['to_user'])){
            $reloginHistory = $reloginHistory->where('to_user',$form['to_user']);
        }
        if (empty($sortOrder)) {
            $sortOrder = 'desc';
        }
        if ($pag == null)
        if($sortOrder == 'desc'){
            $sortOrder = 'asc';
        }elseif($sortOrder == 'asc'){
            $sortOrder = 'desc';
        }
        $reloginHistory = $reloginHistory->orderBy('created_at', $sortOrder)->paginate(20);



        return view('admin.pages.relogin.list', compact('reloginHistory','form','sortOrder'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
