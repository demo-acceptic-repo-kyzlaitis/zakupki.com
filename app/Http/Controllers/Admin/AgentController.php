<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Agent;
use App\Model\Kind;
use App\Model\ProcedureTypes;
use App\Model\Status;
use App\Model\TendersRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AgentController extends Controller
{

    /**
     * показывает новие агенти без модерации
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {

        $agents = Agent::where('status', 'like', 'pending')->orWhere('status', 'like', 'stopped')->orWhere('status', 'like', 'suspended')->orderBy('updated_at', 'desc')->paginate(20);

        return view('admin.pages.agent.list', compact('agents'));
    }


    /**
     * показывает рабочие агенты
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activeIndex() {

        $agents = Agent::where('status', 'like', 'active')->orderBy('updated_at', 'desc')->paginate(20);

        return view('admin.pages.agent.activeList', compact('agents'));
    }

    public function search(Request $request) {

        if(empty($request->get('agent_id')) ^ empty($request->get('organization_id'))) {
            if(!empty($request->get('agent_id'))) {
                $agents = Agent::where('id', $request->get('agent_id'))->paginate(20);
            }
            if(!empty($request->get('organization_id'))) {
                $agents = Agent::where('organization_id', $request->get('organization_id'))->paginate(20);
            }
        } else {
            Session::flash('flash_error', 'Шукати користувачів можна лише по одному з полів');

            return redirect()->back();
        }

        return view('admin.pages.agent.list', compact('agents'));
    }
    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id) {
        $agentModel = Agent::findOrFail($id);

        $codes2015 = $agentModel->codes()->where('type', 1)->get();
        $codes2010 = $agentModel->codes()->where('type', 2)->get();

        $procedureTypes = ProcedureTypes::all()->lists('procedure_name', 'id');
        $statuses       = Status::where('namespace', 'tender')->lists('description', 'id');
        $regions        = TendersRegions::where('status', 1)->lists('region_ua', 'id');
        $kinds          = Kind::all()->lists('name', 'id');

        return view('admin.pages.agent.edit', compact('agentModel', 'codes2015', 'codes2010', 'procedureTypes', 'kinds', 'statuses', 'regions'));
    }


    public function update(Request $request, $id) {
        $agentData = $request->all();


        if(array_key_exists('tender_statuses', $agentData)) {
            $agentData['tender_statuses'] = implode('|', $agentData['tender_statuses']);
        } else {
            $agentData['tender_statuses'] = null;
        }

        if(array_key_exists('kinds', $agentData)) {
            $agentData['kinds'] = implode('|', $agentData['kinds']);
        } else {
            $agentData['kinds'] = null;
        }

        if(array_key_exists('procedure_types', $agentData)) {
            $agentData['procedure_types'] = implode('|', $agentData['procedure_types']);
        } else {
            $agentData['procedure_types'] = null;
        }

        if(array_key_exists('regions', $agentData)) {
            $agentData['regions'] = implode('|', $agentData['regions']);
        } else {
            $agentData['regions'] = null;
        }

        $agent = Agent::findOrFail($id);
        $agentData['status'] = 'active';

        $agent->update($agentData);

        $codes = array_merge_recursive((isset($agentData['codes2015'])) ? $agentData['codes2015'] : [], (isset($agentData['codes2010'])) ? $agentData['codes2010'] : []);


        $agent->codes()->sync($codes);

        return redirect()->back();
    }

}
