<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Model\Classifiers;
use App\Model\Codes;
use App\Model\Currencies;
use App\Model\DocumentType;
use App\Model\Organization;
use App\Model\Plan;
use App\Model\ProcedureTypes;
use App\Model\TendersRegions;
use App\Model\Units;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    /**
     * @param Request $request
     * @param Classifiers $classifier
     * @param $index
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function lot(Request $request, Classifiers $classifier, $index) {

        $template    = $request->get('template');
        $classifiers = $classifier->getNewClassifiersList();
        $documentTypes = DocumentType::whereNamespace('tender')->get();
        $organization  = Organization::find($request->get('organization'));
        $procedureType = ProcedureTypes::find($request->get('proc'));
        $readonly      = '';
        if($procedureType->procurement_method == 'selective') $readonly = 'readonly';

        return view('pages.tender.' . $template . '.lot-form', [
            'index'         => $index,
            'template'      => $template,
            'units'         => Units::lists('description', 'id'),
            'regions'       => TendersRegions::orderBy('id')->active()->lists('region_ua', 'id'),
            'currencies'    => Currencies::lists('currency_description', 'id'),
            'codes'         => Codes::whereParentId('0')->paginate(20),
            'classifiers'   => $classifiers,
            'documentTypes' => $documentTypes,
            'procedureType' => $procedureType,
            'organization'  => $organization,
            'readonly'      => $readonly,
        ]);
    }

    /**
     * @param Request $request
     * @param Classifiers $classifier
     * @param $lot_index
     * @param $index
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function item(Request $request, Classifiers $classifier, $lot_index, $index) {
        $template    = $request->get('template');
        $classifiers = $classifier->getNewClassifiersList();

        $documentTypes = DocumentType::whereNamespace('tender')->get();
        $organization  = Organization::find($request->get('organization'));
        $procedureType = ProcedureTypes::find($request->get('proc'));
        $readonly      = '';

        if($procedureType->procurement_method == 'selective') $readonly = 'readonly';

        return view('pages.tender.' . $template . '.item-form', [
            'lotIndex'      => $lot_index,
            'index'         => $index,
            'units'         => Units::lists('description', 'id'),
            'regions'       => TendersRegions::orderBy('id')->active()->lists('region_ua', 'id'),
            'currencies'    => Currencies::lists('currency_description', 'id'),
            'codes'         => Codes::whereParentId('0')->paginate(20),
            'classifiers'   => $classifiers,
            'template'      => $template,
            'documentTypes' => $documentTypes,
            'procedureType' => $procedureType,
            'organization'  => $organization,
            'readonly'      => $readonly,
        ]);
    }

    /**
     * @param Classifiers $classifier
     * @param $index
     * @param int $plan
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function planItem(Classifiers $classifier, $index, $plan = 0) {
        $plan        = Plan::find($plan);
        $classifiers = $classifier->getNewClassifiersList();

        return view('pages.planning.component.item-form', [
            'index'       => $index,
            'units'       => Units::lists('description', 'id'),
            'currencies'  => Currencies::lists('currency_description', 'id'),
            'codes'       => Codes::whereParentId('0')->paginate(20),
            'classifiers' => $classifiers,
            'plan'        => $plan,
        ]);
    }

    public function feature(Request $request, $namespace, $index) {

        $procedureType = ProcedureTypes::find($request->get('proc'));
        $readonly      = '';

        if($procedureType->procurement_method == 'selective') $readonly = 'readonly';

        return view('pages.tender.open.feature-form', [
            'index'         => $index,
            'namespace'     => $namespace,
            'template'      => $request->get('template'),
            'procedureType' => $procedureType,
            'readonly'      => $readonly,
        ]);
    }

    public function featureValue(Request $request, $namespace, $feature_index, $index) {

        $procedureType = ProcedureTypes::find($request->get('proc'));
        $readonly      = '';

        if($procedureType->procurement_method == 'selective') $readonly = 'readonly';

        return view('pages.tender.open.feature-value-form', [
            'featureIndex'  => $feature_index,
            'index'         => $index,
            'namespace'     => $namespace,
            'procedureType' => $procedureType,
            'readonly'      => $readonly,
        ]);
    }
}
