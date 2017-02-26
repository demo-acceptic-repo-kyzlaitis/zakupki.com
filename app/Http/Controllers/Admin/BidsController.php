<?php

namespace App\Http\Controllers\Admin;

use App\Events\TenderAnswerEvent;
use App\Events\TenderPublishEvent;
use App\Model\Status;
use Validator;

use App\Events\DocumentUploadEvent;
use App\Events\TenderSaveEvent;
use App\Http\Requests\CreateTenderRequest;
use App\Model\Codes;
use App\Model\Document;
use App\Model\DocumentType;
use App\Model\Item;
use App\Model\Organization;
use App\Model\Tender;
use App\Model\Bid;
use App\Model\Currencies;
use App\Model\Units;


use Illuminate\Http\Request;

use Event;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class BidsController extends Controller
{
    public function index(Request $request){

        $form = $request->get('form');
        if (empty($form)) {
            $form = ['mode' => '','name'=>'','organization_id'=>'','type'=>'','status'=>'','tender_id'=>''];
        }
        $mode = $form['mode'];
        $name = $form['name'];
        $organization_id = $form['organization_id'];
        $type = $form['type'];
        $status = $form['status'];
        $tender_id = $form['tender_id'];
        if (!empty($form['mode'])) {
            //      $bids->tenders->where('mode', $form['mode']);
        }

        $bids = Bid::where('access_token','!=','');

        if (!empty($tender_id)){
            $bids = $bids->where('tender_id',$tender_id);
        }
        if (!empty($type)){
            if($type == '2'){
                $bids = $bids->where('bidable_type','App\Model\Lot');
            }elseif($type == '1'){
                $bids = $bids->where('bidable_type','App\Model\Tender');
            }
        }

        $sortField = $request->get('sf');
        $sortOrder = $request->get('so');
        $searchStatus = $request->get('status');
        if (empty($sortField)) {
            $sortField = 'updated_at';
            $sortOrder = 'desc';
        }
        if (!empty($searchStatus)) {
            $bids = $bids->where('status', $searchStatus);
        }

        if (!empty($status)) {
            $bids = $bids->whereHas('tender', function ($query) use($status) {
                $query->where('status', $status);
            });
        }
        if (!empty($organization_id)) {
            $bids = $bids->whereHas('organization', function ($query) use($organization_id) {
                $query->where('id', $organization_id);
            });
        }
        if (!empty($name)) {
            $bids = $bids->whereHas('organization', function ($query) use($name) {
                $query->where('name', $name);
            });
        }
        if (!empty($mode)) {
            $bids = $bids->whereHas('tender', function ($query) use($mode) {
                $query->where('mode', $mode);
            });
        }






        $statuses = Status::where('namespace', 'tender')->get();
        $sortOrder = $sortOrder == 'asc' ? 'desc' : 'asc';
        $bids = $bids->orderBy($sortField, $sortOrder)->paginate(25);
        return view('admin.pages.bids.list', compact('bids', 'form', 'sortField', 'sortOrder','searchStatus','statuses','form'));;
    }

    public function edit($id)
    {
        $bid = Bid::find($id);
        $entity = $bid->bidable;
        $features = [];
        $allValues = [];
        foreach ($bid->values as $value) {
            $features[] = $value->feature;
            foreach ($value->feature->values as $featureValue) {
                $allValues[$featureValue->id] = $featureValue->value;
            }
        }
        return view('admin.pages.bids.edit', ['bid' => $bid, 'entity' => $entity, 'features' => collect($features)->sort(), 'allValues' => collect($allValues)]);
    }
}
