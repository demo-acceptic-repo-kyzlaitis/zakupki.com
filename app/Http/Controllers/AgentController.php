<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Model\Agent;
use App\Model\TendersRegions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AgentController extends Controller
{
    /**
     * Display a listing of the agent.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $agents = Auth::user()->organization->agents()->get();


        return view('pages.agent.list', compact('agents'));
    }

    /**
     * Show the form for creating a new agents.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $regions = TendersRegions::active()->get()->lists('region_ua', 'id');

        return view('pages.agent.create', compact('regions'));
    }

    /**
     * Store a newly created agent in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $agentData = $request->all();

        $agentData['status'] = Agent::$status['pending'];

        $codes = array_merge_recursive((isset($agentData['codes2015'])) ? $agentData['codes2015'] : [], (isset($agentData['codes2010'])) ? $agentData['codes2010'] : []);

        $agentData = new Agent($agentData);
        $agentData->save();//->codes()->sync($codes);

        $agentData->codes()->sync($codes);

        Auth::user()->organization->agents()->save($agentData);

        Session::flash('flash_message', 'Ви створили запит на створення пошукового агента закупівель. Ваш персональний менеджер зв\'яжеться з вами');

        return redirect()->route('agent.index');
    }

    /**
     * Display the specified agent.
     *
     * @param  int $id айди агента
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $agent = Auth::user()->organization->agents()->findOrFail($id);

        if($agent) {
            $history  = $agent->agentHistory();
            if($agent->email_frequency == 'daily') {
                $tendersFoundToday  = $history->with('tender')->whereDate('created_at', '=', Carbon::today()->toDateString())->paginate(10);
            } //else { // weekly
                //$current  = $history->whereDate('created_at', '=', Carbon::week()->toDateString())->paginate(10)->get();
            //}

        }

        return view('pages.agent.show', compact('tendersFoundToday', 'agent'));
    }

    public function archive($id) {
        $agent = Auth::user()->organization->agents()->findOrFail($id);

        if($agent) {
            $history  = $agent->agentHistory();
            if($agent->email_frequency == 'daily') {
                $tendersFoundToday  = $history->with('tender')->whereDate('created_at', '<', Carbon::today()->toDateString())->paginate(10);
            }
        }


        return view('pages.agent.archive', compact('agent', 'tendersFoundToday'));
    }
    
    /**
     * Show the form for editing the specified agent.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $agentModel = Agent::findOrFail($id);

        $codes2015 = $agentModel->codes()->where('type', 1)->get();
        $codes2010 = $agentModel->codes()->where('type', 2)->get();

        return view('pages.agent.edit', compact('agentModel', 'codes2015', 'codes2010'));
    }

    /**
     * Update the specified agent in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $agent           = Auth::user()->organization->agents()->findOrFail($id);
        $agentUpdateData = $request->all();

        $codes = array_merge_recursive((isset($agentUpdateData['codes2015'])) ? $agentUpdateData['codes2015'] : [], (isset($agentUpdateData['codes2010'])) ? $agentUpdateData['codes2010'] : []);

        $agentUpdateData['status'] = Agent::$status['suspended'];
        $agent->update($agentUpdateData);

        $agent->codes()->sync($codes);

        Session::flash('flash_message', 'Ваш пошуковый агент був оновлений');

        return redirect()->route('agent.index');
    }

    /**
     * Remove the specified agent from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $agent = Auth::user()->organization->agets()->findOrFail($id);

        $agent->delete();

        Session::flash('flash_message', 'Видалення агенту пройшло успішно');

        return redirect()->route('agent.index');
    }
}
