<?php

namespace App\Import;

use App\Model\Planning;
use App\Model\Organization;
use App\Model\Codes;
use App\Model\PlanningItem;
use App\Model\Award;
use App\Model\Bid;
use App\Model\Contract;
use App\Model\Currencies;
use App\Model\Document;
use App\Model\Item;
use App\Model\Question;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Plan
{
    protected $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function process()
    {


        DB::beginTransaction();
        DB::commit();
        $data = $this->_data;
            if(0 == Planning::where('cbd_id', '=', $data['id'])->count()){
                $planning = new Planning;
                $planning->organization = Organization::where('identifier', '=', $data['identifier']['id']);
                $codes = [];
                $codes[] = $data['classification']['id'];
                if (isset($data['additionalClassifications']) && is_array($data['additionalClassifications'])){
                    foreach($data['additionalClassifications'] as $addclassifictors){
                        $codeId = Codes::where('code', '=', $addclassifictors['id'])->firstOrFail();
                        $codes[] = $codeId->id;
                    }
                }
                $planning->codes()->sync($codes);
                $planning->project_name = $data['budget']['project']['name'];
                $planning->project_description = $data['budget']['description'];
                $planning->amount = $data["budget"]['amount'];
                $planning->amount_without_vat = $data["budget"]['amountNet'];
                $planning->planning_date = $data["tender"]["tenderPeriod"]["startDate"];
                $planning->cbd_id = $data['id'];
                $planning->save();
                if(isset($data['items']) && is_array(($data['items']))){
                    $planningItem = new PlanningItem;
                    $planningItem->unit_id = $data['items']['unit']['code'];
                    $planningItem->planning_id = $planning->id;
                    $planningItem->quantity = $data['items']['quantity'];
                    $planningItem->description = $data['items']['description'];
                   // $planningItem->planning_date = date('Y-m-d H:i:s',strtotime($item['date_start']));
                    $planningItem->cbd_id = $data['items']['id'];
                    $code = [];
                    $code[] = $data['items']['classification']['id'];
                    if (isset($data['items']['additionalClassifications']) && is_array($data['items']['additionalClassifications'])){
                        foreach($data['items']['additionalClassifications'] as $plannClassificator){
                            $cId = Codes::where('code', '=', $plannClassificator['id'])->firstOrFail();
                            $code[] = $cId->id;
                        }
                    }
                    $planning->codes()->sync($code);
                    $planningItem->save();
                }
            }else{
                $planning = Planning::where('cbd_id', '=', $data['id'])->get();
                $planning->organization = Organization::where('identifier', '=', $data['identifier']['id']);
                $codes = [];
                $codes[] = $data['classification']['id'];
                if (isset($data['additionalClassifications']) && is_array($data['additionalClassifications'])){
                    foreach($data['additionalClassifications'] as $addclassifictors){
                        $codeId = Codes::where('code', '=', $addclassifictors['id'])->firstOrFail();
                        $codes[] = $codeId->id;
                    }
                }
                $planning->codes()->sync($codes);
                $planning->project_name = $data['budget']['project']['name'];
                $planning->project_description = $data['budget']['description'];
                $planning->amount = $data["budget"]['amount'];
                $planning->amount_without_vat = $data["budget"]['amountNet'];
                $planning->planning_date = $data["tender"]["tenderPeriod"]["startDate"];
                $planning->save();
                if(isset($data['items']) && is_array(($data['items']))){
                    if(0 == PlanningItem::where('cbd_id', '=', $data['items']['id'])->count()){
                        $planningItem = new PlanningItem;
                        $planningItem->unit_id = $data['items']['unit']['code'];
                        $planningItem->planning_id = $planning->id;
                        $planningItem->quantity = $data['items']['quantity'];
                        $planningItem->description = $data['items']['description'];
                        // $planningItem->planning_date = date('Y-m-d H:i:s',strtotime($item['date_start']));
                        $planningItem->cbd_id = $data['items']['id'];
                        $code = [];
                        $code[] = $data['items']['classification']['id'];
                        if (isset($data['items']['additionalClassifications']) && is_array($data['items']['additionalClassifications'])){
                            foreach($data['items']['additionalClassifications'] as $plannClassificator){
                                $cId = Codes::where('code', '=', $plannClassificator['id'])->firstOrFail();
                                $code[] = $cId->id;
                            }
                        }
                        $planning->codes()->sync($code);
                        $planningItem->save();
                    }else{
                        $planningItem = PlanningItem::where('cbd_id', '=', $data['items']['id'])->get();
                        $planningItem->unit_id = $data['items']['unit']['code'];
                        $planningItem->planning_id = $planning->id;
                        $planningItem->quantity = $data['items']['quantity'];
                        $planningItem->description = $data['items']['description'];
                        // $planningItem->planning_date = date('Y-m-d H:i:s',strtotime($item['date_start']));
                        $planningItem->cbd_id = $data['items']['id'];
                        $code = [];
                        $code[] = $data['items']['classification']['id'];
                        if (isset($data['items']['additionalClassifications']) && is_array($data['items']['additionalClassifications'])){
                            foreach($data['items']['additionalClassifications'] as $plannClassificator){
                                $cId = Codes::where('code', '=', $plannClassificator['id'])->firstOrFail();
                                $code[] = $cId->id;
                            }
                        }
                        $planning->codes()->sync($code);
                        $planningItem->save();
                    }
                }

            }
        try {

        } catch (\Exception $e) {
            DB::rollBack();
            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());
        }

        return false;
    }
}