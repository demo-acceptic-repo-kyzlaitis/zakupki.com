<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Api\Struct\ContractDocument;
use App\Api\Struct\Tender as ApiTender;
use App\Events\AwardDocUploadEvent;
use App\Events\AwardSaveEvent;
use App\Events\CancelActivateEvent;
use App\Events\CancelDocUploadEvent;
use App\Events\CancelSaveEvent;
use App\Events\ContractDocUploadEvent;
use App\Events\ContractSaveEvent;
use App\Events\DocumentUploadEvent;
use App\Events\PatchChangeDocumentEvent;
use App\Events\PlanSaveEvent;
use App\Events\TenderAnswerEvent;
use App\Events\TenderSaveEvent;
use App\Http\Requests;
use App\Jobs\ActivateAward;
use App\Model\Award;
use App\Model\AwardDocuments;
use App\Model\Bid;
use App\Model\Cancellation;
use App\Model\CancellationDocuments;
use App\Model\ClassifierCpv;
use App\Model\Codes;
use App\Model\Contract;
use App\Model\ContractChange;
use App\Model\ContractDocuments;
use App\Model\Currencies;
use App\Model\Document;
use App\Model\Feature;
use App\Model\Item;
use App\Model\Kind;
use App\Model\Lot;
use App\Model\Organization;
use App\Model\Plan;
use App\Model\PlanItem;
use App\Model\ProcedureTypes;
use App\Model\Question;
use App\Model\RationaleType;
use App\Model\Tender;
use App\Model\TendersRegions;
use App\Model\Units;
use App\Model\User;
use Event;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Validator;

class ApiController extends Controller
{

    protected function _saveFeatures($entity, $data, $tenderId)
    {
        $featureIds = [];
        if (!empty($data)) {
            foreach ($data as $featureData) {
                if (!empty($featureData['code'])) {
                    $feature = $entity->features->find($featureData['code']);
                    $feature->update($featureData);
                } else {
                    $featureData['tender_id'] = $tenderId;
                    $feature = new Feature($featureData);
                    $entity->features()->save($feature);
                }
                $featureValuesIds = [];
                foreach ($featureData['enum'] as $featureValue) {
                    $featureValue = $feature->values()->updateOrCreate(['feature_id' => $feature->id, 'value' => $featureValue['value'], 'title' => $featureValue['title']], $featureValue);

                    $featureValuesIds[] = $featureValue->id;
                }
                $featureIds[] = $feature->id;
                $feature->values()->whereNotIn('id', $featureValuesIds)->delete();
            }
        }
        $entity->features()->whereNotIn('id', $featureIds)->delete();
        return $featureIds;
    }

	// Validation section
    protected function _docsChanged($files)
    {
        foreach ($files as $file) {
            if ($file != null)
                return true;
        }
        return false;
    }

    protected function _validateDocsChanged($tenderData)
    {
        if (isset($tenderData['newfiles']) && $this->_docsChanged($tenderData['newfiles']))
            return true;

        if (isset($tenderData['tender']['files']) && $this->_docsChanged($tenderData['tender']['files']))
            return true;

        foreach ($tenderData['lots'] as $lot) {
            if (isset($lot['files']) && $this->_docsChanged($lot['files']))
                return true;

            foreach ($lot['items'] as $item) {
                if (isset($item['files']) && $this->_docsChanged($item['files']))
                    return true;
            }
        }

        return false;
    }

    protected function _validateFeatures($featureData, $checkVar)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'indicator' => 'required'
        ];
        $messages = [
            'value.min' => 'Значення нецінового критерію повинно бути більше нуля',
            'value.numeric' => 'Поле "Вага нецінового критерію" повинно бути числом',
            'title.required' => "Введіть назву показника",
            'description.required' => "Введіть коментар до показника",
            'indicator.required' => 'Додайте, будь ласка, опцію з нульовим значенням',
        ];
        if ($checkVar != false) {
            foreach ($checkVar as $key => $chek) {
                $rules['indicator_' . $key] = 'required';
                $messages['indicator_' . $key . '.required'] = 'Додайте, будь ласка, опцію з нульовим значенням (' . $checkVar[$key] . ')';
            }
        }
        if (Input::get('type_id') == 3 || Input::get('type_id') == 10) {  // aboveTresholdEU || competitiveDialogueEU
            $rules['title_en'] = 'required';
            $messages['title_en.required'] = 'Введіть назву показника англійською';
        }

        $validate = Validator::make($featureData, $rules, $messages);
        if (!$validate->fails()) {
            foreach ($featureData['values'] as $valueData) {
                $rules = [
                    'value' => 'numeric|min:0',
                    'title' => 'required'
                ];
                $messages = [
                    'value.min' => "Значення нецінового критерію повинно бути більше нуля",
                    'title.required' => "Введить назву опції",
                ];

                $validate = Validator::make($valueData, $rules, $messages);
                if ($validate->fails()) {
                    return $validate;
                }
            }
        }

        return $validate;
    }


    protected function _getItemsCodes($tenderData)
    {
        $codes = [];
        foreach ($tenderData['lots'] as $lotData) {
            foreach ($lotData['items'] as $itemData) {
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
            }
        }

        return $codes;
    }

    protected function _validateCodes($tenderData)
    {
        $codesIds = $this->_getItemsCodes($tenderData);
        $codes = Codes::whereIn('id', $codesIds)->get();
        $cpv = $dkpp = $cpvAll = $dkppAll = [];
        foreach ($codes as $code) {
            $add = false;
            if ($code->type == 1) {
                $currentCode = substr($code->code, 0, 3);
                if (!empty($cpvAll)) {
                    foreach ($cpvAll as $key => $val) {
                        if (strpos($currentCode, $key) !== 0 && strpos($key, $currentCode) !== 0) {
                            $add = true;
                            break;
                        }
                    }
                } else {
                    $add = true;
                }
                $cpvAll[$currentCode] = 1;
                if ($add) {
                    $cpv[$currentCode] = 1;
                }
            } else {
                $currentCode = substr($code->code, 0, 7);
                if (!empty($dkppAll)) {
                    foreach ($dkppAll as $key => $val) {
                        if (strpos($currentCode, $key) !== 0 && strpos($key, $currentCode) !== 0) {
                            $add = true;
                            break;
                        }
                    }
                } else {
                    $add = true;
                }
                $dkppAll[$currentCode] = 1;

                if ($add) {
                    $dkpp[$currentCode] = 1;
                }
            }
        }

        $tenderData['cpv_codes_count'] = count($cpv);
        $tenderData['dkpp_codes_count'] = (count($dkpp) == 0) ? 1 : count($dkpp);
        return $tenderData;
    }

    protected function _validateTender($tenderData)
    {
        if (!empty($tenderData['amount'])) {
            $amount = $tenderData['amount'];
        } else {
            $amount = 0;
            foreach ($tenderData['lots'] as $lot) {
                $amount += $lot['value']['amount'];
            }
            $tenderData['amount'] = $amount;
        }

        $rules = [
            'title' => 'required|max:255',
            'currency_id' => 'required|integer',
//            'features_amount' => 'integer|max:30',
            'cpv_codes_count' => 'in:1',
            'dkpp_codes_count' => 'in:1',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|regex:"\+380\d{3,12}"',
            'contact_email' => 'required|email',
            'contact_url' => 'url',
            'amount' => 'numeric|min:0',
            //'identifier'       => 'required',
        ];

        /*if ($tenderData['type_id'] == 2 || $tenderData['type_id'] == 3) {
            $tenderData['tenderDifference'] =  floor((strtotime($tenderData['tender_end_date']) - time()) / (86400 / env('API_ACCELERATOR')));
            $tenderDuration = 7;
            $rules['tenderDifference'] = 'integer|min:'.$tenderDuration;
        }*/

        $tenderID = (isset($tenderData['tender_id'])) ? intval($tenderData['tender_id']) : null;
        if ($tenderData['type_id'] == 1 && !$tenderID) {
//            $tenderData['enquiryDifference'] = floor((strtotime($tenderData['enquiry_end_date']) - strtotime($tenderData['enquiry_start_date'])) / (86400 / env('API_ACCELERATOR')));
//            $tenderData['tenderDifference'] =  floor((strtotime($tenderData['tender_end_date']) - strtotime($tenderData['tender_start_date'])) / (86400 / env('API_ACCELERATOR')));

//            $tenderData['enquiryDifference'] = getWorkdaysBetweenDates(strtotime($tenderData['enquiry_end_date']), strtotime($tenderData['enquiry_start_date']));
//            $tenderData['tenderDifference'] =  getWorkdaysBetweenDates(strtotime($tenderData['tender_end_date']), strtotime($tenderData['tender_start_date']));

            if ($amount < 50000) {
                $tenderDuration = $enquiryDuration = 2;
            } else {
                $enquiryDuration = 4;
                $tenderDuration = 3;
            }

//            $rules['tenderDifference'] = 'integer|min:'.$tenderDuration;
//            $rules['enquiryDifference'] = 'integer|min:'.$enquiryDuration;
            //$rules['enquiry_start_date'] = 'required|date|after:' . date('Y-m-d');
            $minEnquiryEndDate = getDateWithWorkdays(time(), $enquiryDuration);
            if ($minEnquiryEndDate < strtotime($tenderData['enquiry_end_date'])) {
                $rules['tender_start_date'] = 'required|date|after:enquiry_end_date';
                $rules['tender_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', getDateWithWorkdays(strtotime($tenderData['enquiry_end_date']), $tenderDuration));
            } else {
                $rules['enquiry_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', $minEnquiryEndDate);
            }
        }

        if ($tenderData['type_id'] == 8) {
            if ($tenderID) {
                $tender = Tender::findOrFail($tenderID);
                $tenderStartDate = strtotime($tender->tender_start_date);
                $daysFromStartToEnd = getWorkdaysBetweenDates($tenderStartDate, getDateWithWorkdays(time(), 3));
                $minDayEnd = 3;
                if ($daysFromStartToEnd < 7)
                    $minDayEnd = 7;
            } else {
                $minDayEnd = 7;
            }
            $rules['tender_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', getDateWithWorkdays(time(), $minDayEnd));
        }

        if (in_array($tenderData['type_id'], [2, 3, 9, 10])) {
            $tenderID = intval($tenderData['tender_id']);
            if ($tenderID) {
                $tender = Tender::findOrFail($tenderID);
                $currentDate = strtotime($tender->tender_start_date);
            } else {
                $currentDate = time();
            }
            $minDayEnd = ($tenderData['type_id'] == 2 || $tenderData['type_id'] == 9) ? 15 : 30;
            $minDateEndTimestamp = $currentDate + $minDayEnd * 86400;
            if ($tenderID) {
                $dateToEndTender = time() + 7 * 86400;
                if ($dateToEndTender > $minDateEndTimestamp)
                    $minDateEndTimestamp = $dateToEndTender;
            }
            $minDateEnd = Carbon::createFromTimestamp($minDateEndTimestamp / env('API_ACCELERATOR') - 1);
            $rules['tender_end_date'] = 'required|date|after:' . $minDateEnd;
        }

        $messages = [
            'cpv_codes_count.in' => "Оберіть, будь ласка, код класифікатору зі списку.",
            'contact_phone.regex' => 'Введіть, будь ласка, Ваш номер телефону в форматі +380998877665',
            'contact_phone.required' => 'Поле "Телефон" необхідно заповнити',
            'dkpp_codes_count.in' => "У всіх лотах мають співпадати перші п'ять символів коду класифікатора ДК 016:2010 і перші три символи коду класифікатора ДК 021:2015",
            'amount.min' => "Очікувана вартість закупівлі не може бути меншою, ніж 3000 грн.",
            'amount.numeric' => 'Поле "Бюджет лоту" повинно бути числом',
            'type_id.in' => "Шановний користувач, на нашому майданчику на даний момент доступні лише допорогові закупівлі",
            //'identifier.required'    => "Поле \"Код ЄДРПОУ\" необхідно заповнити",
            'contact_email.required' => 'Поле "Email" необхідно заповнити',
            'contact_email.email' => 'Введіть, будь ласка, Вашу електронну пошту в форматі priklad@domain.com',
        ];

        if ($tenderData['type_id'] == 1 && !$tenderID) {
            $messages['enquiryDifference.min'] = 'Період уточнень повинен бути не менше, ніж ' . $enquiryDuration . ' ' . Lang::choice('день | дні | днів', $enquiryDuration, [], 'ru');
            $messages['tenderDifference.min'] = 'Період подачі пропозицій повинен бути не менше, ніж ' . $tenderDuration . ' ' . Lang::choice('день | дні | днів', $tenderDuration, [], 'ru');
        }

        /*if ($tenderData['type_id'] == 2 || $tenderData['type_id'] == 3) {
            if ($tenderID) {
                $messages['tenderDifference.min'] = 'Кінцевий строк подачі пропозицій може бути подовжений не менш ніж на ' . $tenderDuration . ' ' . Lang::choice('день | дні | днів', $tenderDuration, [], 'ru');
            } else {
                $messages['tenderDifference.min'] = 'Кінцевий строк подачі пропозицій може бути не раніше ніж через ' . $tenderDuration . ' ' . Lang::choice('день | дні | днів', $tenderDuration, [], 'ru');
            }
        }*/


        if (in_array($tenderData['type_id'], [2, 3, 9, 10])) {
            $messages['tender_end_date.required'] = 'Поле "Закінчення періоду подачі пропозицій" необхідно заповнити';
            $messages['tender_end_date.min'] = 'Період подачі пропозицій повинен бути не раніше, ніж ' . $minDayEnd;
            $messages['tender_end_date'] = 'Період подачі пропозицій повинен бути не раніше, ніж ' . $minDayEnd;
        }

        if ($tenderData['type_id'] == 3 || $tenderData['type_id'] == 10) {
            $rules['title_en'] = 'required';
            $messages['title_en.required'] = "Поле \"Назва англійсьокю\" повино бути заповнене.";
        }
        if ($tenderData['type_id'] == 8) {

            $timestamp = time();
            $minTimeTxt = date('d.m.Y H:i', $timestamp + 604800);
            $minTime = $timestamp + 604800;
            $tenderData['tender_end_date'] = strtotime($tenderData['tender_end_date']);
            $rules['tender_end_date'] = 'required';
            $messages['tender_end_date.required'] = 'Поле "Закінчення періоду подачі пропозицій" необхідно заповнити';
            $rules['tender_end_date'] = 'numeric|min:' . $minTime;
            $messages['tender_end_date.min'] = 'Кінцевий строк подання тендерних пропозицій повинен бути не раніше, ніж ' . $minTimeTxt;
        }

        if (env('APP_ENV') != 'server') {
            //unset($rules['tenderDifference']);
            //unset($rules['enquiryDifference']);
        }

//         $tenderData = $this->_validateCodes($tenderData);
//         $this->_validateCodes($tenderData);
//         $tenderData['features_amount'] = $this->_getFeaturesAmount($tenderData);


        $featureValidator = $this->_validateFeature($tenderData);
        if ($featureValidator->fails()) {
            return $featureValidator;
        }

        if (Input::get('type_id') == 9 || Input::get('type_id') == 10)
            $maxFeatureSum = 99;
        else
            $maxFeatureSum = 30;

        $featureRules = [
            'tender_feature_sum' => 'integer|max:' . $maxFeatureSum,
        ];

//         foreach ($tenderData['features_amount'] as $lotAmount) {
//             $validator = Validator::make(['tender_feature_sum' => $lotAmount['tender_feature_sum']], $featureRules, [
//                 'tender_feature_sum.min' => 'Додаткові нецінові показники не можуть бути менше нуля',
//                 'tender_feature_sum.max' => 'Додаткові нецінові показники в сумі не можуть перевищувати ' . $maxFeatureSum . '% (перевищення в лоті ' . $lotAmount['lot_id'] . ')',
//             ]);

//             if ($validator->fails()) {
//                 return $validator;
//             }
//         }


        return Validator::make($tenderData, $rules, $messages);
    }

    protected function _validateLot($lotData, $index, $procurementTypeId, $tenderTypeId = 0)
    {
        $index++;
        $lotData['items_count'] = isset($lotData['items']) ? count($lotData['items']) : 0;
        if ($procurementTypeId == 1) {
            $minStep = $lotData['amount'] * 0.5 / 100;
            $maxStep = $lotData['amount'] * 3 / 100;
        } else {
            $minStep = 0;
            $maxStep = $lotData['amount'];
        }
        $rules = [
            'amount' => 'required|numeric',
            'minimal_step' => 'required|numeric|max:' . $maxStep . '|min:' . $minStep,
            'items_count' => 'numeric|min:1',
            'title' => 'required',
        ];

        $messages = [
            'minimal_step.max' => "Максимальний крок аукціону в лоті $index не повинен перевищувати 3%.",
            'minimal_step.min' => "Мінімальний крок аукціону в лоті $index повинен бути не менше 0.5% .",
            'minimal_step.required' => "Поле \"Мінімальний крок аукціону \" в лоті $index повино бути заповнене.",
            'items_count.min' => "Лот $index повинен містити хоча б один предмет закупівлі.",
            'amount.required' => 'Поле "Бюджет лоту" потрібно заповнити',
            'amount.numeric' => 'Поле "Бюджет лоту" повинно бути числом.',
            'title.required' => "Поле \"Назва\" в лоті $index повино бути заповнене.",
        ];

        if ($tenderTypeId == 3 || $tenderTypeId == 10) {
            $rules['title_en'] = 'required';
            $messages['title_en.required'] = "Поле \"Назва англійсьокю\" в лоті $index повино бути заповнене.";
        }


        if (array_key_exists('guarantee_type', $lotData) && $procurementTypeId && $lotData['guarantee_type'] === "dbg") {
            $maxGuarantee = $lotData['amount'] * 3 / 100;
            $minGuarantee = $lotData['amount'] * 0 / 100;
            $messages['guarantee_amount.min'] = "Розмір тендерного забезпечення в лоті " . $index . " не може бути від'ємним";
            $messages['guarantee_amount.max'] = "Розмір тендерного забезпечення не може перевищувати 3 %  від очікуваної вартості закупівлі";
            $messages['guarantee_amount.required'] = "Поле 'Розмір гарантії' повинно бути заповнено";
            if ($procurementTypeId === '1') {
                $maxGuarantee = $lotData['amount'] * 0.5 / 100;
                $messages['guarantee_amount.max'] = "Розмір тендерного забезпечення не може перевищувати 0.5 %  від очікуваної вартості закупівлі";
            }
            $rules['guarantee_amount'] = 'required|numeric|max:' . $maxGuarantee . '|min:' . $minGuarantee;
        }

        $featureValidator = $this->_validateFeature($lotData);
        if ($featureValidator->fails()) {
            return $featureValidator;
        }

        return Validator::make($lotData, $rules, $messages);
    }

    protected function _validateItem($itemData, $itemIndex, $lotIndex, $tenderTypeId = 0)
    {
        $itemIndex++;
        $lotIndex++;

        $rules = [
            'quantity' => 'required|integer',
            'dkpp' => 'required',
            'cpv' => 'required',
            'unit_id' => 'required',
            'delivery_date_start' => 'date',
            'delivery_date_end' => 'date|after:delivery_date_start',
            'codes.1.id' => 'required',
        ];

        if ($tenderTypeId != 9 && $tenderTypeId != 10) {
            $rules['delivery_date_start'] = 'date';
            $rules['delivery_date_end'] = 'date|after:delivery_date_start';
        }


        $descriptions = [
            'quantity' => 'Кількість',
            'dkpp' => 'Код класифікатора ДКПП',
            'cpv' => 'Код класифікатора CPV',
            'unit_id' => 'Одиниця виміру',
            'delivery_date_start' => 'Початок періоду поставки',
            'delivery_date_end' => 'Кінець періоду поставки',
            //'codes.0.id' => 'Код CPV',
            'codes.1.id' => 'Код ДКПП',
        ];

        if ($itemData['region_id'] != (int)0) {
            $rules['postal_code'] = 'required|digits_between:5,5';
            $rules['locality'] = 'required';
            $rules['delivery_address'] = 'required';

            $descriptions['postal_code'] = 'Індекс';
            $descriptions['locality'] = 'Місто';
            $descriptions['delivery_address'] = 'Адреса';
        }
        $messages = [
            'cpv.required' => "Оберіть, будь ласка, код класифікатору зі списк.",
            'quantity.integer' => "Лот №$lotIndex, товар №$itemIndex. Поле \":attribute\" повинно бути цілим числом.",
            'codes.1.id.required' => "Лот №$lotIndex, товар №$itemIndex. :attribute необхідно вибрати зі списку. Введіть в поле перші цифри коду для отримання підказки",
        ];
        if ($tenderTypeId != 4 && $tenderTypeId != 5 && $tenderTypeId != 6 && $itemData['delivery_date_start'] != '') {
            $rules['delivery_date_start'] .= '|after:' . date('d.m.Y');
            $messages['delivery_date_start.after'] = 'Початок періоду поставки повинен бути не раніше ніж ' . date('d.m.Y', time(date('d.m.Y')) + 86400);
        }
        if (array_key_exists('additionalClassifier', $itemData)) {
            $rules['additionalClassifier'] = 'required';
            $rules['codes.' . intval($itemData['additionalClassifier']) . '.id'] = 'required';
            $descriptions['additionalClassifier'] = 'Додатковий класифікатор';
            $messages['codes.' . intval($itemData['additionalClassifier']) . '.id.required'] = "Лот №$lotIndex, товар №$itemIndex. Додатковий класифікатор необхідно вибрати зі списку. Введіть в поле перші цифри коду для отримання підказки";
        }

        $this->_validateFeature($itemData);

        return Validator::make($itemData, $rules, $messages, $descriptions);
    }

    protected function _validate($data)
    {
        $validate = $this->_validateTender($data);
        if (!$validate->fails()) {
            $procedureType = \App\Model\ProcedureTypes::find($data['type_id']);
            foreach ($data['lots'] as $lotIndex => $lot) {
                if (isset($procedureType) && $procedureType->procurement_method == 'open') { //procurement_method = open
                    $validate = $this->_validateLot($lot, $lotIndex, array_key_exists('procurement_type_id', $data) ? $data['procurement_type_id'] : false, $data['type_id']);
                    if (!$validate->fails()) {
                        foreach ($lot['items'] as $itemIndex => $item) {
                            $validate = $this->_validateItem($item, $itemIndex, $lotIndex, $data['type_id']);
                            if ($validate->fails()) {
                                return $validate;
                            }
                        }
                    } else {
                        return $validate;
                    }
                } else {
                    foreach ($lot['items'] as $itemIndex => $item) {
                        $validate = $this->_validateItem($item, $itemIndex, $lotIndex, $data['type_id']);
                        if ($validate->fails()) {
                            return $validate;
                        }
                    }
                }
            }
        } else {
            return $validate;
        }

        return $validate;
    }

    protected function _validateFeature($data)
    {
        $i = 0;
        $checkVar = false;
        $countrLots = 0;
        $x = 0;

        if (isset($data['lots'])) {
            foreach ($data['lots'] as $lot) {
                $countrLots++;
                if (isset($lot['features'])) {
                    foreach ($lot['features'] as $future) {
                        foreach ($future['values'] as $val) {
                            $tmp[] = $val['value'];
                        }
                        if (in_array(0, $tmp) == false) {
                            $checkVar[$x] = 'Лот №' . $countrLots;
                        }
                        if (isset($tmp)) {
                            unset($tmp);
                        }

                        $x++;
                    }

                }
                if ($lot['items']) {
                    $countrItems = 1;
                    foreach ($lot['items'] as $item) {
                        if (isset($item['features'])) {
                            foreach ($item['features'] as $fut) {

                                foreach ($fut['values'] as $val) {
                                    $tmp[] = $val['value'];
                                }
                                if (in_array(0, $tmp) == false) {
                                    $checkVar[$x] = 'Товар №' . $countrItems . ' Лот №' . $countrLots;
                                }
                                if (isset($tmp)) {
                                    unset($tmp);
                                }
                                $countrItems++;
                            }
                        }
                    }
                }
            }
        }
        if (isset($data['features']) && !empty($checkVar)) {

            foreach ($data['features'] as $featureData) {

                foreach ($featureData['values'] as $value) {
                    if ($value['value'] == 0) {
                        $i = 1;
                    }
                }
                if ($i != 1) {
                    $featureData['indicator'] = null;
                } else {
                    $featureData['indicator'] = 'true';
                }
                if (!empty($checkVar)) {
                    foreach ($checkVar as $key => $chek) {
                        $featureData['indicator_' . $key] = null;
                    }
                }
                $validate = $this->_validateFeatures($featureData, $checkVar);
                return $validate;
            }
        } elseif (isset($data['features']) && empty($checkVar)) {
            foreach ($data['features'] as $featureData) {

                foreach ($featureData['values'] as $value) {
                    if ($value['value'] == 0) {
                        $i = 1;
                    }
                }
                if ($i != 1) {
                    $featureData['indicator'] = null;
                } else {
                    $featureData['indicator'] = 'true';
                }
                $validate = $this->_validateFeatures($featureData, $checkVar);
                return $validate;
            }
        } elseif (!isset($data['features']) && !empty($checkVar)) {

            $featureData['title'] = true;
            $featureData['description'] = true;
            $featureData['indicator'] = 'true';
            foreach ($checkVar as $key => $chek) {
                $featureData['indicator_' . $key] = null;
            }
            $validate = $this->_validateFeatures($featureData, $checkVar);
            return $validate;
        }
        $validate = Validator::make($data, []);
        return $validate;
    }

    public function tenders(Request $request) {
        $data = $request->all();
        $data = $data['data'];
        

        $tenderData = [
            'title'              => $data['title'],
            'description'        => isset($data['description']) ? $data['description'] : '',
            'amount'             => isset($data['value']['amount']) ? $data['value']['amount'] : 0,
            'currency_id'        => \App\Model\Currencies::where('currency_code', 'UAH')->first()->id,
            'tax_included'       => isset($data['value']['valueAddedTaxIncluded']) ? $data['value']['valueAddedTaxIncluded'] : false,
            'minimal_step'       => isset($data['minimalStep']['amount']) ? $data['minimalStep']['amount'] : 0,
            'enquiry_start_date' => isset($data['enquiryPeriod']['startDate']) ? $data['enquiryPeriod']['startDate'] : false,
            'enquiry_end_date'   => isset($data['enquiryPeriod']['endDate']) ? $data['enquiryPeriod']['endDate'] : '',
            'tender_start_date'  => isset($data['tenderPeriod']['startDate']) ? $data['tenderPeriod']['startDate'] : false,
            'tender_end_date'    => isset($data['tenderPeriod']['endDate']) ? $data['tenderPeriod']['endDate'] : '',
        ];

        // START VALIDATION
        $errors        = [];
        $errorResponse = json_decode('{
              "UT_id": "\\"\\"",
              "errors": [{
                  "organization" : [],
                  "tender": [],
                  "items": [],
                  "lots": []
              }]
            }', true);

        $organizationValidator = Validator::make($data['procuringEntity'], Organization::$validation, Organization::$messages);
        if($organizationValidator->fails()) {
            $orgArrErr = $organizationValidator->getMessageBag()->toArray();
            foreach ($orgArrErr as $key => $val) {
                $errorResponse['errors'][0]['organization'] = $val;
            }
        }

        $tenderData = $this->_getTenderData($data, 2);
        
        $tenderValidator = $this->_validateTender($tenderData);
        if($tenderValidator->fails()) {
            $tenderArrError = $tenderValidator->getMessageBag()->toArray();
            foreach ($tenderArrError as $key => $val) {
                $errorResponse['errors'][0]['tender'] = $val;
            }
        }

        $isItemsFailed = false;

//         foreach ($data['items'] as $item) {
//             $itemValidator = Validator::make($item, Item::$validation, Item::$messages);
//             if($itemValidator->fails()) {
//                 $isItemsFailed = true;
//                 $entity        = [];
//                 $itemArrErr    = $itemValidator->getMessageBag()->toArray();
//                 foreach ($itemArrErr as $key => $val) {
//                     $entity[$key] = $val[0];
//                 }
//                 $errorResponse['errors'][0]['items'][] = $entity;
//             }
//         }

        if($organizationValidator->fails() || $tenderValidator->fails()) {
            return response()->json($errorResponse);
        }
        //END VALIDATION


        $tender = new Tender($this->_getTenderData($data, 2));

        $organization = Organization::where('user_id' , '=', Auth::id())->first();

        if(!$organization) {
            return response()->json([
                'UT_id'  => isset($tender->id) ? $tender->id : '',
                'lots'   => isset($returnLots) ? $returnLots : '',
                'errors' => 'Organization not found',
            ], 404);
        }

        $tender = $organization->tenders()->save($tender);

        $lots = collect($data['lots']);
        $returnLots = [];
		
        if (isset($data['features'])) {
        	$tendererFeatures = collect($data['features'])->where('featureOf', 'tenderer');
        	$features['tenderer'] = $this->_saveFeatures($tender, $tendererFeatures, $tender->id);
        } else {
        	$features[] = [];
        }
        
        foreach ($lots as $lot) {
            $lotData['description']  = $lot['description'];
            $lotData['title']        = $lot['title'];
            $lotData['amount']       = $lot['value']['amount'];
            $lotData['currency_id']  = Currencies::where('currency_code', $lot['value']['currency'])->first()->id;
            $lotData['tax_included'] = $lot['value']['valueAddedTaxIncluded'] ? 1 : 0;
            $lotData['minimal_step'] = $lot['minimalStep']['amount'];
            $lotEntity               = new Lot($lotData);
            $lotEntity->save();
            $items = collect($data['items'])->where('lotNumber', $lot['lotNumber']);
            $tender->lots()->save($lotEntity);
            $lotInfo['lotNumber'] = $lot['lotNumber'];
            $lotInfo['lotId']     = $lotEntity->id;
            
            if (isset($data['features'])) {
            	$lotFeatures = collect($data['features'])->where('featureOf', 'lot')->where('relatedItem', $lot['lotNumber']);
            	$features['lots'] = $this->_saveFeatures($lotEntity, $lotFeatures, $lotEntity->id);
            }
            
            foreach ($items as $item) {
                $unitModel = \App\Model\Units::where('code', $item['unit']['code'])->first();
                $itemData = [
                    "cbd_id"      => isset($item['id']) ? $item['id'] : '',
                    "description" => $item['description'],
                    "quantity"    => $item['quantity'],
                    "unit_id"     => $unitModel->id,
                    "tender_id"   => $tender->id,
                ];

                if(isset($item['deliveryDate'])) {
                    $itemData['delivery_date_start'] = isset($item['deliveryDate']['startDate']) ? $item['deliveryDate']['startDate'] : null;
                    $itemData['delivery_date_end']   = isset($item['deliveryDate']['endDate']) ? $item['deliveryDate']['endDate'] : null;
                } else {
                    $itemData['delivery_date_start'] = null;
                    $itemData['delivery_date_end']   = null;
                }

                $regionId = 0;
                if(isset($item['deliveryAddress'])) {
                    if(isset($item['deliveryAddress']['region'])) {
                        $regionName = trim(str_replace('область', '', $item['deliveryAddress']['region']));
                        $region     = \App\Model\TendersRegions::orWhere('region_ua', 'LIKE', '%' . $regionName . '%')->orWhere('region_search', 'LIKE', '%' . $regionName . '%')->first();
                        if($region) {
                            $regionId = $region->id;
                        }
                    }
                    $itemData['country_id']       = 1;
                    $itemData['region_id']        = $regionId;
                    $itemData['region_name']      = isset($item['deliveryAddress']['region']) ? $item['deliveryAddress']['region'] : '';
                    $itemData['postal_code']      = isset($item['deliveryAddress']['postalCode']) ? $item['deliveryAddress']['postalCode'] : '';
                    $itemData['locality']         = isset($item['deliveryAddress']['locality']) ? $item['deliveryAddress']['locality'] : '';
                    $itemData['delivery_address'] = isset($item['deliveryAddress']['streetAddress']) ? $item['deliveryAddress']['streetAddress'] : '';
                }
                $codes   = [];
                $codes[] = \App\Model\Codes::where('code', $item['classification']['id'])->first()->id;
                foreach ($item['additionalClassifications'] as $additionalCode) {
                    if(is_array($additionalCode)) {
                        $codes[] = \App\Model\Codes::where('code', $additionalCode['id'])->first()->id;
                    }
                }

                $itemEntity = new Item($itemData);
                $itemEntity->save();

                //todo добавить позже нужно как то определить к кому шо относится
//                $itemFeatures = collect($data['features'])->where('featureOf', 'item');
//                $features['items'] = $this->_saveFeatures($itemEntity, $itemFeatures, $item->id);

                $itemEntity->codes()->sync($codes);
                $lotEntity->items()->save($itemEntity);
                $lotInfo['itemsIds'][] = $itemEntity->id;
            } // END items FOREACH
            $returnLots[] = $lotInfo;
            unset($lotInfo);
        } // END lot FOREACH


        //сохранить лоты
        //сохранить айтемы

        $tender->status = 'published';
        $tender->save();
        Event::fire(new TenderSaveEvent($tender));

        return response()->json([
            'UT_id'  => isset($tender->id) ? $tender->id : '',
            'lots'   => $returnLots,
            'features' => $features,
            'errors' => $errors,
        ], 200);
    }

    public function updateTender(Request $request, $tid) {
        $tender = Tender::findOrFail($tid);
        $data = $request->get('data');
        
        $organization = Organization::where('user_id' , '=', Auth::id())->first();
        
        if ($organization->id != $tender->organization_id) {
        	$errorResponse['errors'][0]['organization'] = 'Orzanization of user and tender didn\'t match';
        	return response()->json($errorResponse);
        }
        
        // START VALIDATION
        $errors        = [];
        $errorResponse = json_decode('{
              "UT_id": "\\"\\"",
              "errors": [{
                  "organization" : [],
                  "tender": [],
                  "items": [],
                  "lots": []
              }]
            }', true);
        
        $organizationValidator = Validator::make($data['procuringEntity'], Organization::$validation, Organization::$messages);
        if($organizationValidator->fails()) {
        	$orgArrErr = $organizationValidator->getMessageBag()->toArray();
        	foreach ($orgArrErr as $key => $val) {
        		$errorResponse['errors'][0]['organization'] = $val;
        	}
        }
        
        $tenderData = $this->_getTenderData($data, 2);
        
        $tenderValidator = $this->_validateTender($tenderData);
        if($tenderValidator->fails()) {
        	$tenderArrError = $tenderValidator->getMessageBag()->toArray();
        	foreach ($tenderArrError as $key => $val) {
        		$errorResponse['errors'][0]['tender'] = $val;
        	}
        }
        
        $isItemsFailed = false;
        
        //         foreach ($data['items'] as $item) {
        //             $itemValidator = Validator::make($item, Item::$validation, Item::$messages);
        //             if($itemValidator->fails()) {
        //                 $isItemsFailed = true;
        //                 $entity        = [];
        //                 $itemArrErr    = $itemValidator->getMessageBag()->toArray();
        //                 foreach ($itemArrErr as $key => $val) {
        //                     $entity[$key] = $val[0];
        //                 }
        //                 $errorResponse['errors'][0]['items'][] = $entity;
        //             }
        //         }
        
        if($organizationValidator->fails() || $tenderValidator->fails()) {
        	return response()->json($errorResponse);
        }
        //END VALIDATION
        
        $tender->update($this->_getTenderData($data, 2));

        $errors        = [];
        $errorResponse = json_decode('{
              "UT_id": "\\"\\"",
              "errors": [{
                  "organization" : [],
                  "tender": [],
                  "items": [],
                  "lots": []
              }]
            }', true);

        if (isset($data['features'])) {
        	$tendererFeatures = collect($data['features'])->where('featureOf', 'tenderer');
        	$features['tenderer'] = $this->_saveFeatures($tender, $tendererFeatures, $tender->id);
        } else {
        	$features[] = [];
        }
        foreach($data['lots'] as $lot) {
            $items = collect($data['items'])->where('lotNumber', $lot['lotNumber']);

            $lotData['description']  = $lot['description'];
            $lotData['title']        = $lot['title'];
            $lotData['amount']       = $lot['value']['amount'];
            $lotData['currency_id']  = Currencies::where('currency_code', $lot['value']['currency'])->first()->id;
            $lotData['tax_included'] = $lot['value']['valueAddedTaxIncluded'] ? 1 : 0;
            $lotData['minimal_step'] = $lot['minimalStep']['amount'];

            if(isset($lot['id'])) {
                $lotEntity = Lot::findOrFail($lot['id'])->update($lotData);
            } else {
                $lotEntity               = new Lot($lotData);
                $lotEntity = $tender->lots()->save($lotEntity);
            }

            $lotInfo['lotNumber'] = $lot['lotNumber'];
            $lotInfo['lotId']     = $lotEntity->id;

            if (isset($data['features'])) {
            	$lotFeatures = collect($data['features'])->where('featureOf', 'lot')->where('relatedItem', $lot['lotNumber']);
            	$features['lots'] = $this->_saveFeatures($lotEntity, $lotFeatures, $lotEntity->id);
            }
            
            foreach($items as $item) {

                $unitModel = \App\Model\Units::where('code', $item['unit']['code'])->first();

                $itemData = [
                    "cbd_id"      => isset($item['id']) ? $item['id'] : '',
                    "description" => $item['description'],
                    "quantity"    => $item['quantity'],
                    "unit_id"     => $unitModel->id,
                    "tender_id"   => $tender->id,
                ];

                if(isset($item['deliveryDate'])) {
                    $itemData['delivery_date_start'] = isset($item['deliveryDate']['startDate']) ? $item['deliveryDate']['startDate'] : null;
                    $itemData['delivery_date_end']   = isset($item['deliveryDate']['endDate']) ? $item['deliveryDate']['endDate'] : null;
                } else {
                    $itemData['delivery_date_start'] = null;
                    $itemData['delivery_date_end']   = null;
                }

                $regionId = 0;
                if(isset($item['deliveryAddress'])) {
                    if(isset($item['deliveryAddress']['region'])) {
                        $regionName = trim(str_replace('область', '', $item['deliveryAddress']['region']));
                        $region     = \App\Model\TendersRegions::orWhere('region_ua', 'LIKE', '%' . $regionName . '%')->orWhere('region_search', 'LIKE', '%' . $regionName . '%')->first();
                        if($region) {
                            $regionId = $region->id;
                        }
                    }
                    $itemData['country_id']       = 1;
                    $itemData['region_id']        = $regionId;
                    $itemData['region_name']      = isset($item['deliveryAddress']['region']) ? $item['deliveryAddress']['region'] : '';
                    $itemData['postal_code']      = isset($item['deliveryAddress']['postalCode']) ? $item['deliveryAddress']['postalCode'] : '';
                    $itemData['locality']         = isset($item['deliveryAddress']['locality']) ? $item['deliveryAddress']['locality'] : '';
                    $itemData['delivery_address'] = isset($item['deliveryAddress']['streetAddress']) ? $item['deliveryAddress']['streetAddress'] : '';
                }
                $codes[] = \App\Model\Codes::where('code', $item['classification']['id'])->first()->id;
                foreach ($item['additionalClassifications'] as $additionalCode) {
                    if(is_array($additionalCode)) {
                        $codes[] = \App\Model\Codes::where('code', $additionalCode['id'])->first()->id;
                    }
                }
                
                if(isset($item['id'])) {
                	$itemEntity = Item::findOrFail($item['id'])->update($itemData);
                } else {
                	$itemEntity = null;
                }

                if($itemEntity) { // UPDATEING ITEM
                    $itemEntity->update($itemData);
                } else {  // CREAGING ITEM
                    $itemEntity = new Item($itemData);
                    $itemEntity->save($itemData);
                    $itemEntity->codes()->sync($codes);
                    $lotEntity->items()->save($itemEntity);
                    $lotInfo['itemsIds'][] = $itemEntity->id;
                }
            } // END ITEMS FOREACH
            $returnLots[] = $lotInfo;
            unset($lotInfo);
        } // END LOTS FOREACH
        
        $tender->status = 'published';
        $tender->save();
        Event::fire(new TenderSaveEvent($tender));

        return response()->json([
            'UT_id'  => isset($tender->id) ? $tender->id : '',
            'lots'   => $returnLots,
            'errors' => $errors,
        ], 200);
    }

    public function getTender($id) {
        $tender = Tender::with('lots')->where('id', $id)->get();

        return response()->json(['data' => $tender->toArray()[0]]);
    }

    /**
     * Грузит документы. Если тип дока не был указан то ставим ноль
     *
     * @param Request $request
     * @param $id
     * @return Document|bool
     */
    public function uploadDocument(Request $request, $id) {
        $tender = Tender::findOrFail($id);
        $files  = $request->files->all();
        $docIds = [];

//        if($files === NULL && $request->get('api_test') !== NULL) { //для теста
//            $file = new UploadedFile(storage_path() .'/app/test/rHTZUw1ODyo.jpg', 'rHTZUw1ODyo.jpg');
//        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        foreach ($files as $file) {

            $path = DIRECTORY_SEPARATOR . 'tender' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
                'type_id'            => $request->get('type_id') !== null ? $request->get('type_id') : 0,
            ];

            $newDoc = new Document($params);

            $doc = $tender->documents()->save($newDoc);

            $docIds[] = $doc->id;
            Event::fire(new DocumentUploadEvent($doc));
        }

        return response()->json(['ids' => $docIds]);
    }

    public function uploadLotDocument(Request $request, $id, $lotId) {
        $lot    = Lot::findOrFail($lotId);
        $files  = $request->files->all();
        $docIds = [];

        foreach ($files as $file) {
            $path = DIRECTORY_SEPARATOR . 'tenders' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
                'type_id'            => $request->get('type_id') !== null ? $request->get('type_id') : 0,
                'tender_id'          => $lot->tender->id,
            ];

            $newDoc = new Document($params);

            $doc = $lot->documents()->save($newDoc);

            $docIds[] = $doc->id;
            Event::fire(new DocumentUploadEvent($doc));
        }

        return response()->json(['ids' => $docIds]);
    }

    public function updateLotDocument(Request $request, $id, $lotId, $document_id) {
        $relatedTenderId = Lot::findOrFail($lotId)->tender->id;
        if($relatedTenderId !== $id) {
            return response()->json(['errors' => ['Лот не пренадлежит тендеру']]);
        }

        $file = $request->file('file');

        $path = DIRECTORY_SEPARATOR . 'tender' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();

        Storage::disk('documents')->put($path, File::get($file));

        $document        = Document::findOrFail($document_id);
        $document->path  = $path;
        $document->title = $file->getClientOriginalName();
        $document->update();

        Event::fire(new DocumentUploadEvent($document));

        return response()->json(['status' => 'updated']);
    }

    public function getLotDocuments(Request $request, $id, $lotId) {
        $docs = Lot::findOrFail($lotId)->documents()->get();

        return response()->json(['documents' => $docs]);
    }

    public function uploadItemDocument(Request $request, $id, $lotId, $itemId) {
        $item   = Item::findOrFail($itemId);
        $files  = $request->files->all();
        $docIds = [];

        foreach ($files as $file) {
            $path = DIRECTORY_SEPARATOR . 'tenders' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
                'type_id'            => $request->get('type_id') !== null ? $request->get('type_id') : 0,
                'tender_id'          => $item->tender->id,
            ];

            $newDoc = new Document($params);

            $doc = $item->documents()->save($newDoc);

            $docIds[] = $doc->id;
            Event::fire(new DocumentUploadEvent($doc));
        }

        return response()->json(['ids' => $docIds]);
    }

    public function updateItemDocument(Request $request, $id, $lotId, $itemId, $docId) {
        $relatedTenderId = Item::findOrFail($itemId)->tender->id;
        if($relatedTenderId !== $id) {
            return response()->json(['errors' => ['Айтем не пренадлежит тендеру']]);
        }

        $file = $request->file('file');

        $path = DIRECTORY_SEPARATOR . 'tender' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();

        Storage::disk('documents')->put($path, File::get($file));

        $document        = Document::findOrFail($docId);
        $document->path  = $path;
        $document->title = $file->getClientOriginalName();
        $document->update();

        Event::fire(new DocumentUploadEvent($document));

        return response()->json(['status' => 'updated']);
    }

    public function getItemDocument(Request $request, $id, $lotId, $itemId) {
        $docs = Item::findOrFail($itemId)->documents()->get();

        return response()->json(['documents' => $docs]);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $document_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDocument(Request $request, $id, $document_id) {
        $file = $request->file('file');

        $path = DIRECTORY_SEPARATOR . 'tender' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();

        Storage::disk('documents')->put($path, File::get($file));

        $document        = Document::findOrFail($document_id);
        $document->path  = $path;
        $document->title = $file->getClientOriginalName();
        $document->update();

        Event::fire(new DocumentUploadEvent($document));

        return response()->json(['status' => 'updated']);
    }

    public function getDocument($id) {
        $docs = Document::select('id', 'title', 'format', 'url', 'created_at')->where('documentable_id', $id)->get();

        return response()->json(['documents' => $docs]);
    }

    public function putAnswer(Request $request, $tenderId, $questionId) {
        $tender   = Tender::findOrFail($tenderId);
        $question = \App\Model\Question::findOrFail($questionId);
        if($tender->canQuestion()) {
            $data             = $request->all();
            $textAnswer       = $data['data']['answer'];
            $question->answer = $textAnswer;
            $question->save();
            Event::fire(new TenderAnswerEvent($question));
        } else {
            abort(403, 'Нельзя отвечать на вопросы если закончился период уточенений');
        }

        return $question;
    }

    public function askQuestion(Request $request, $id) {
        $data = $request->get('data');

        return 0;
    }

    public function getQuestions($tenderId) {
        $data = Tender::findOrFail($tenderId)->questions;
        foreach ($data as &$item) {
            $item['date'] = $item->created_at;
            unset($item['created_at']);
            unset($item['updated_at']);
        }

        return ['data' => $data];
    }

    public function getQuestion($id, $qid) {
        $question         = Question::find($qid)->toArray();
        $question['date'] = $question['created_at'];
        unset($question['created_at']);
        unset($question['updated_at']);

        return $question;
    }

    public function getSend() {
        $tender        = Tender::find(13);
        $api           = new ApiTender($tender);
        $array['data'] = $api->getData();
        $client        = new \GuzzleHttp\Client(['verify' => false]);
        try {
            //$response = $client->post('https://api-sandbox.openprocurement.org/api/0/tenders', ['json' => $array, 'auth' =>  ['4c5056be703a43c5ba1f01bacf5d4e7b', '4c5056be703a43c5ba1f01bacf5d4e7b']]);
            $response = $client->patch('https://api-sandbox.openprocurement.org/api/0/tenders/' . $tender->cbd_id . '?acc_token=' . $tender->access_token, [
                'json' => $array,
                'auth' => [
                    '4c5056be703a43c5ba1f01bacf5d4e7b',
                    '4c5056be703a43c5ba1f01bacf5d4e7b',
                ],
            ]);
        } catch (ClientException  $e) {

            if($e->hasResponse()) {
                dd($e->getResponse());
            }
        }
        $data = (string)$response->getBody();

        $d                = json_decode($data, true);
        $tender->tenderID = $d['data']['tenderID'];
        $tender->cbd_id   = $d['data']['id'];
        $tender->status   = $d['data']['status'];
        $tender->save();
        die;

    }

    /**
     * Переводит статус отмены тендера в пендинг
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelTender(Request $request, $id) {
        $data       = $request->get('data');
        $entityName = $data['entity_type'];

        if($entityName == 'tender') {
            $entity = Tender::findOrFail($id);
        } else {
            $entity = Lot::with('tender')->findOrFail($id);
            if($entity->tender->organization->id != Auth::user()->organization->id) {
                return response()->json(['status' => 403], ['status' => 403]);
            }
        }

        $cancellation         = new Cancellation($data);
        $cancellation->status = 'pending';
        $entity->cancel()->save($cancellation);

        Event::fire(new CancelSaveEvent($cancellation));

        return response()->json(['cancellation-id' => $cancellation->id]);
    }

    public function getCancelDocuments(Request $request, $id, $cancellation_id) {
        $cancelDocuments = CancellationDocuments::select('id', 'title', 'url', 'created_at')->where('cancellation_id', $cancellation_id)->get();

        return response()->json(['documents' => $cancelDocuments]);
    }

    /**
     * Возвращает масив id документов которые были добавленны в дб закупок
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCancelDoc(Request $request, $id, $cancellation_id) {
        $files  = $request->files->all();
        $ids    = [];
        $cancel = Cancellation::findOrFail($cancellation_id);

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        foreach ($files as $index => $file) {
            $path = '/cancel/' . $cancellation_id . '/' . time() . '_' . $file->getClientOriginalName();

            Storage::disk('documents')->put($path, File::get($file));

            $doc = new CancellationDocuments([
                'path'  => $path,
                'title' => $file->getClientOriginalName(),
            ]);
            $cancel->documents()->save($doc);
            $ids[] = $doc->id;
            Event::fire(new CancelDocUploadEvent($doc));
        }

        return response()->json(['ids' => $ids]);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $cancellation_id
     * @param $document_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCancelDoc(Request $request, $id, $cancellation_id, $document_id) {
        $file = $request->file('file');

        $path = '/cancel/' . $cancellation_id . '/' . time() . '_' . $file->getClientOriginalName();

        Storage::disk('documents')->put($path, File::get($file));

        $cancellationDocument        = CancellationDocuments::findOrFail($document_id);
        $cancellationDocument->path  = $path;
        $cancellationDocument->title = $file->getClientOriginalName();

        $cancellationDocument->update();

        Event::fire(new CancelDocUploadEvent($cancellationDocument));

        return response()->json(['status' => 'updated']);
    }

    public function activateTenderCancellation(Request $request, $id, $cancellation_id) {

        $cancel = Cancellation::find($cancellation_id);

        $cancel->status = 'active';
        $cancel->save();
        Event::fire(new CancelActivateEvent($cancel));


//        id: uid, auto-generated
//reason: string, multilingual, required The reason, why Tender is being cancelled.
//        status: stringPossible values are:
//pending: Default. The request is being prepared.
//        active: Cancellation activated.

        return response()->json([
            'id'     => $cancel->id,
            'reason' => $cancel->reason,
            'status' => $cancel->status,
        ]);
    }

    public function getAwards(Request $request, $id) {
        $awards     = Tender::findOrFail($id)->awards;
        $returnData = [];
        $data       = [];
        foreach ($awards as $award) {

            $bidableEntity = $award->bid()->first()->bidable()->getResults();
            $organization  = $award->bid()->first()->organization()->first();
            $documents     = $award->documents()->getResults();


            $data['status']      = $award->status;
            $data['lotID']       = $bidableEntity->cbd_id;
            $data['suppliers'][] = [
                'name'         => $organization->name,
                'identifier'   => [
                    'id'        => $organization->indetifier,
                    'schema'    => 'UA-EDR',
                    'uri'       => $organization->contact_url,
                    'legalName' => $organization->name,
                ],
                'address'      => [
                    'streetAddress' => $organization->street_address,
                    'locality'      => $organization->locality,
                    'region'        => $organization->region_name,
                    'postalCode'    => $organization->postal_code,
                    'countryName'   => 'Україна',
                ],
                'contactPoint' => [
                    'name'      => $organization->name,
                    'email'     => $organization->contact_name,
                    'telephone' => $organization->contact_phone,
                    'faxNumber' => $organization->contact_fax,
                    'url'       => $organization->contact_url,
                ],
            ];
            $data['id']          = $award->id;
            $data['bid_id']      = $award->bid_id;
            $data['title']       = $bidableEntity->title;
            $data['description'] = $bidableEntity->description;
//            $data['date'] = $award->contract();
            $data['value']['amount']                = $award->bid->amount;
            $data['value']['currency']              = Currencies::findOrFail($bidableEntity->currency_id)->currency_code;
            $data['value']['valueAddedTaxIncluded'] = $bidableEntity->tax_included === 1;

            foreach ($documents as $document) {

                $documentData['id']            = $document->id;
                $documentData['documentType']  = $document->format;
                $documentData['title']         = $document->title;
                $documentData['format']        = $document->format;
                $documentData['url']           = $document->url;
                $documentData['datePublished'] = $document->created_at;
                $documentData['dateModified']  = $document->updated_at;
                $documentData['language']      = 'uk';
                $documentData['documentOf']    = 'tender';

                $data['documents'][] = $documentData;
                unset($documentData);
            }

            $returnData[] = $data;
            unset($data);
        }

        return response()->json(['data' => $returnData]);
    }

    public function getAward(Request $request, $id, $aid) {
        $award = Tender::findOrFail($id)->awards()->where('id', $aid)->first();
        $award->documents;

        return response()->json(['data' => $award]);
    }

    public function acceptAward(Request $request, $tid, $aid) {
        $award = Tender::findOrFail($tid)->awards()->where('id', $aid)->first();

        $documents = $award->documents;
        $errors = [];

        if(count($documents) < 1) {
            $errors['no_documents'] = 'no documents';
        }

        foreach($documents as $document) {
            if($document->url === '') {
                $error['id'] = $document->id;
                $error['msg'] = 'not synchronized';
                $errors[] = $error;
            }
        }
        $award->status = 'activate';

        $award->save();
        $this->dispatch(new ActivateAward($award));

        return ['data' => $award, 'errors' => $errors];
    }

    public function reject(Request $request, $tid, $aid) {
        $award = Tender::findOrFail($tid)->awards()->where('id', $aid)->first();

        $award->status = 'unsuccessful';

        $award->save();
        Event::fire(new AwardSaveEvent($award));

        return ['data' => $award];
    }

    public function addAwardProtocol(Request $request, $tid, $aid) {
        $award = Award::findOrFail($aid);
        $files = $request->files->all();

        foreach ($files as $file) {
            $path = DIRECTORY_SEPARATOR . 'awards' . DIRECTORY_SEPARATOR . $aid . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
                'type_id'            => $request->get('type_id') !== null ? $request->get('type_id') : 13,//повидомлення про ришення если ничего не было указано
            ];

            $awardDocument = new AwardDocuments($params);
            $award->documents()->save($awardDocument);

            $docIds[] = $awardDocument->id;

            Event::fire(new AwardDocUploadEvent($awardDocument));
        }

        return response()->json(['ids' => $docIds]);
    }

    public function updateAwardProtocol(Request $request, $tid, $aid, $did) {
        $award = Award::findOrFail($aid);
        $files = $request->files->all();

        $file = $request->file('file');

        $path = DIRECTORY_SEPARATOR . 'awards' . DIRECTORY_SEPARATOR . $aid . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();

        Storage::disk('documents')->put($path, File::get($file));

        $document        = AwardDocuments::findOrFail($did);
        $document->path  = $path;
        $document->title = $file->getClientOriginalName();
        $document->update();

        Event::fire(new AwardDocUploadEvent($document));

        return response()->json(['status' => 'updated']);
    }

    public function cancelLot(Request $request, $tid, $lid) {
        $lot = Lot::findOrFail($lid);

        $data = $request->get('data');

        $cancellation         = new Cancellation($data);
        $cancellation->status = 'pending';
        $lot->cancel()->save($cancellation);

        Event::fire(new CancelSaveEvent($cancellation));

        return response()->json(['cancellation-id' => $cancellation->id]);
    }

    public function addCancelLotDoc(Request $request, $tid, $lid, $cid) {
        $files  = $request->files->all();
        $ids    = [];
        $cancel = Cancellation::findOrFail($cid);

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        foreach ($files as $index => $file) {
            $path = '/cancel/' . $cid . '/' . time() . '_' . $file->getClientOriginalName();

            Storage::disk('documents')->put($path, File::get($file));

            $doc = new CancellationDocuments([
                'path'  => $path,
                'title' => $file->getClientOriginalName(),
            ]);
            $cancel->documents()->save($doc);
            $ids[] = $doc->id;
            Event::fire(new CancelDocUploadEvent($doc));
        }

        return response()->json(['ids' => $ids]);
    }

    public function updateCancelLotDoc(Request $request, $tid, $lid, $cid) {
        $file = $request->file('file');

        $path = '/cancel/' . $cid . '/' . time() . '_' . $file->getClientOriginalName();

        Storage::disk('documents')->put($path, File::get($file));

        $cancellationDocument        = CancellationDocuments::findOrFail($cid);
        $cancellationDocument->path  = $path;
        $cancellationDocument->title = $file->getClientOriginalName();

        $cancellationDocument->update();

        Event::fire(new CancelDocUploadEvent($cancellationDocument));

        return response()->json(['status' => 'updated']);
    }

    public function activateLotCancellation(Request $request, $tid, $lid, $cid) {
        $cancel = Cancellation::find($cid);

        $cancel->status = 'active';
        $cancel->save();
        Event::fire(new CancelActivateEvent($cancel));


//        id: uid, auto-generated
//reason: string, multilingual, required The reason, why Tender is being cancelled.
//        status: stringPossible values are:
//pending: Default. The request is being prepared.
//        active: Cancellation activated.

        return response()->json([
            'id'     => $cancel->id,
            'reason' => $cancel->reason,
            'status' => $cancel->status,
        ]);
    }

    public function getLotQuestions(Request $request, $tdi, $lid) {
        $data = Lot::findOrFail($lid)->questions;
        foreach ($data as &$item) {
            $item['date'] = $item->created_at;
            unset($item['created_at']);
            unset($item['updated_at']);
        }

        return ['data' => $data];
    }

    public function putLotAnswer(Request $request, $tdi, $lid, $qid) {
        $tender   = Lot::findOrFail($lid);
        $question = \App\Model\Question::findOrFail($qid);
        if($tender->canQuestion()) {
            $data             = $request->all();
            $textAnswer       = $data['data']['answer'];
            $question->answer = $textAnswer;
            $question->save();
            Event::fire(new TenderAnswerEvent($question));
        } else {
            abort(403, 'Нельзя отвечать на вопросы если закончился период уточенений');
        }

        return $question;
    }

    public function getLotQuestion(Request $request, $tdi, $lid, $qid) {
        $question         = Question::find($qid)->toArray();
        $question['date'] = $question['created_at'];
        unset($question['created_at']);
        unset($question['updated_at']);

        return $question;
    }

    public function getBid(Request $request, $tid, $b_id) {
        $bid          = Bid::findOrFail($b_id);
        $organization = $bid->organization;
        $documents    = $bid->documents()->getResults();

        $docs = [];
        foreach ($documents as $document) {

            $documentData['id']            = $document->id;
            $documentData['documentType']  = 'commercialProposal';
            $documentData['title']         = $document->title;
            $documentData['format']        = $document->format;
            $documentData['url']           = $document->url;
            $documentData['datePublished'] = $document->created_at;
            $documentData['dateModified']  = $document->updated_at;
            $documentData['language']      = 'uk';
            $documentData['documentOf']    = 'lot';

            $docs[] = $documentData;
            unset($documentData);
        }

        $returnData = [
            'tenderers'        => [
                [
                    'name'         => $organization->name,
                    'identifier'   => [
                        'id'        => $organization->indetifier,
                        'schema'    => 'UA-EDR',
                        'uri'       => $organization->contact_url,
                        'legalName' => $organization->name,
                    ],
                    'address'      => [
                        'streetAddress' => $organization->street_address,
                        'locality'      => $organization->locality,
                        'region'        => $organization->region_name,
                        'postalCode'    => $organization->postal_code,
                        'countryName'   => 'Україна',
                    ],
                    'contactPoint' => [
                        'name'      => $organization->name,
                        'email'     => $organization->contact_name,
                        'telephone' => $organization->contact_phone,
                        'faxNumber' => $organization->contact_fax,
                        'url'       => $organization->contact_url,
                    ],
                ],
            ],
            'date'             => '',
            'id'               => $bid->id,
            'status'           => $bid->status,
            'documents'        => $docs,
            'parameters'       => '',
            'lotValues'        => [
                [
                    'relatedLot' => $bid->bidable()->getResults()->id,
                    'date'       => '',
                    'value'      => [
                        'currency'              => $bid->currency->currency_code,
                        'amount'                => $bid->amount,
                        'valueAddedTaxIncluded' => $bid->tax_included,
                    ],
                ],
            ],
            'participationUrl' => '',
        ];

        return response()->json(['data' => $returnData]);
    }

    public function getContracts(Request $request, $tid) {
        $contracts    = Contract::where('tender_id', $tid)->get();
        $contractData = [];

        foreach ($contracts as $contract) {

            $items        = $contract->award->bid->bidable->items;
            $organization = $contract->award->bid->organization;

            $contractEntity = [
                'status' => $contract->status,
            ];

            $contractEntity['suppliers'][] = [
                'name'         => $organization->name,
                'identifier'   => [
                    'id'        => $organization->indetifier,
                    'schema'    => 'UA-EDR',
                    'uri'       => $organization->contact_url,
                    'legalName' => $organization->name,
                ],
                'address'      => [
                    'streetAddress' => $organization->street_address,
                    'locality'      => $organization->locality,
                    'region'        => $organization->region_name,
                    'postalCode'    => $organization->postal_code,
                    'countryName'   => 'Україна',
                ],
                'contactPoint' => [
                    'name'      => $organization->name,
                    'email'     => $organization->contact_name,
                    'telephone' => $organization->contact_phone,
                    'faxNumber' => $organization->contact_fax,
                    'url'       => $organization->contact_url,
                ],
            ];
            $contractEntity['period']      = [
                'startDate' => $contract->period_date_start,
                'endDate'   => $contract->period_date_end,
            ];
            $contractEntity['value']       = [
                'currency'              => $contract->tender->currency->currency_code,
                'amount'                => $contract->amount,
                'valueAddedTaxIncluded' => $contract->tender->tax_included === 1,
            ];
            $contractEntity['dateSigned']  = $contract->date_signed;
            $contractEntity['date']        = $contract->updated_at;
            $contractEntity['awardID']     = $contract->award->id;
            $contractEntity['id']          = $contract->id;
            $contractEntity['contractID']  = $contract->contractID;
            foreach ($items as $item) {
                $itemsData       = [
                    'relatedLot'     => $item->lot_id,
                    'description'    => $item->description,
                    'classification' => [],
                ];
                $additionalClass = [];
                foreach ($item->codes as $code) {
                    if($code->classifier->scheme === 'CPV') {
                        $cpvClassification = [
                            'scheme'      => $code->classifier->scheme,
                            'description' => $code->description,
                            'id'          => $code->code,
                        ];
                    } else {
                        $additionalClass = [
                            'scheme'      => $code->classifier->scheme,
                            'description' => $code->description,
                            'id'          => $code->code,
                        ];
                    }
                }

                $itemsData['classification']            = $cpvClassification;
                $itemsData['additionalClassifications'] = $additionalClass;
                $itemsData['deliveryAddress']           = [
                    'postalCode'    => $item->postal_code,
                    'countryName'   => 'Україна',
                    'streetAddress' => $item->delivery_address,
                    'locality'      => $item->locality,
                    'region'        => $item->region->region_ua,
                ];
                $itemsData['deliveryDate']              = [
                    'startDate' => $item->delivery_date_start,
                    'endDate'   => $item->delivery_date_end,
                ];
                $itemsData['id']                        = $item->id;
                $itemsData['unit']                      = [
                    'code' => $item->unit->code,
                    'name' => $item->unit->description,
                ];
                $itemsData['quantity']                  = $item->quantity;
                $contractEntity['items'][]              = $itemsData;
            } //END of ITEMS loop

            $contractData[] = $contractEntity;

        }

        return response()->json(['data' => $contractData]);
    }

    public function getContract(Request $request, $tid, $cid) {
        $contract = Contract::find($cid);

        $items        = $contract->award->bid->bidable->items;
        $organization = $contract->award->bid->organization;

        $contractEntity = [
            'status' => $contract->status,
        ];

        $contractEntity['suppliers'][] = [
            'name'         => $organization->name,
            'identifier'   => [
                'id'        => $organization->indetifier,
                'schema'    => 'UA-EDR',
                'uri'       => $organization->contact_url,
                'legalName' => $organization->name,
            ],
            'address'      => [
                'streetAddress' => $organization->street_address,
                'locality'      => $organization->locality,
                'region'        => $organization->region_name,
                'postalCode'    => $organization->postal_code,
                'countryName'   => 'Україна',
            ],
            'contactPoint' => [
                'name'      => $organization->name,
                'email'     => $organization->contact_name,
                'telephone' => $organization->contact_phone,
                'faxNumber' => $organization->contact_fax,
                'url'       => $organization->contact_url,
            ],
        ];
        $contractEntity['period']      = [
            'startDate' => $contract->period_date_start,
            'endDate'   => $contract->period_date_end,
        ];
        $contractEntity['value']       = [
            'currency'              => $contract->tender->currency->currency_code,
            'amount'                => $contract->amount,
            'valueAddedTaxIncluded' => $contract->tender->tax_included === 1,
        ];
        $contractEntity['dateSigned']  = $contract->date_signed;
        $contractEntity['date']        = $contract->updated_at;
        $contractEntity['awardID']     = $contract->award->id;
        $contractEntity['id']          = $contract->id;
        $contractEntity['contractID']  = $contract->contractID;
        foreach ($items as $item) {
            $itemsData       = [
                'relatedLot'     => $item->lot_id,
                'description'    => $item->description,
                'classification' => [],
            ];
            $additionalClass = [];
            foreach ($item->codes as $code) {
                if($code->classifier->scheme === 'CPV') {
                    $cpvClassification = [
                        'scheme'      => $code->classifier->scheme,
                        'description' => $code->description,
                        'id'          => $code->code,
                    ];
                } else {
                    $additionalClass = [
                        'scheme'      => $code->classifier->scheme,
                        'description' => $code->description,
                        'id'          => $code->code,
                    ];
                }
            }

            $itemsData['classification']            = $cpvClassification;
            $itemsData['additionalClassifications'] = $additionalClass;
            $itemsData['deliveryAddress']           = [
                'postalCode'    => $item->postal_code,
                'countryName'   => 'Україна',
                'streetAddress' => $item->delivery_address,
                'locality'      => $item->locality,
                'region'        => $item->region->region_ua,
            ];
            $itemsData['deliveryDate']              = [
                'startDate' => $item->delivery_date_start,
                'endDate'   => $item->delivery_date_end,
            ];
            $itemsData['id']                        = $item->id;
            $itemsData['unit']                      = [
                'code' => $item->unit->code,
                'name' => $item->unit->description,
            ];
            $itemsData['quantity']                  = $item->quantity;
            $contractEntity['items'][]              = $itemsData;
        } //END of ITEMS loop

        foreach($contract->documents as $document) {
            $contractEntity['documents'][] = [
                'id'           => $document->id,
                'orig_id'      => $document->orig_id,
                'documentType' => $document->type->document_type,
                'title'        => $document->title,
                'format'       => $document->format,
                'url'          => $document->url,
            ];
        }

        return response()->json(['data' => $contractEntity]);
    }

    public function addDetailsToContract(Request $request, $tid, $cid) {
        $contract = Contract::findOrFail($cid);
        $errors = [];

        $data = $request->get('data');


        $rules = [
            'date_signed' => 'required|date|before:' . date('Y-m-d H:i:s'),
        ];

        $messages = [
            'date_signed.before' => "Конракт не може бути підписаний майбутньою датою.",
        ];

        if ($contract->award->complaint_date_end != null) {
            if (isset($data['date_signed'])) {
                $rules['date_signed'] .= '|after:' . $contract->award->complaint_date_end;
                $messages['date_signed.after'] = 'Конракт може бути підписаний після закінчення періоду оскарження. (' . $contract->award->complaint_date_end . ')';
            }
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->toArray();
            return response()->json(['data' => $contract, 'errors' => $errors]);
        }

        $contract->update([
            'contract_number'   => $data['contractNumber'],
            'date_signed'       => $data['dateSigned'],
            'period_date_start' => $data['period']['startDate'],
            'period_date_end'   => $data['period']['endDate'],
            'amount'            => $data['value']['amount'],
        ]);

        Event::fire(new ContractSaveEvent($contract));

        return response()->json(['data' => $contract]);
    }


    public function addDocumentToContract(Request $request, $tid, $cid) {
        $contract = Contract::findOrFail($cid);
        $files  = $request->files->all();
        $docIds = [];

//        if($files === NULL && $request->get('api_test') !== NULL) { //для теста
//            $file = new UploadedFile(storage_path() .'/app/test/rHTZUw1ODyo.jpg', 'rHTZUw1ODyo.jpg');
//        }

        foreach ($files as $file) {

            $path = DIRECTORY_SEPARATOR . 'contract' . DIRECTORY_SEPARATOR . $cid . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
                'type_id'            => $request->get('type_id') !== null ? $request->get('type_id') : 0,
            ];

            $newDoc = new ContractDocuments($params);

            $doc = $contract->documents()->save($newDoc);

            $docIds[] = $doc->id;
            Event::fire(new ContractDocUploadEvent($doc));
        }

        return response()->json(['ids' => $docIds]);
    }

    public function activateContract(Request $request, $tid, $cid) {
        $contract = Contract::findOrFail($cid);

        //todo dataSign должна быть после комплейнт период
        $contract->status = 'active';
        $contract->save();
        Event::fire(new ContractSaveEvent($contract));

        if ($contract->access_token == '') {
            $this->dispatch((new \App\Jobs\GetContractCredentials($contract->cbd_id))->delay(300));
            return response()->json(['data' => 'request for access token']);
        }


        return response()->json(['data' => 'access_token exist']);
    }

    public function terminateContract(Request $request, $tid, $cid) {
        $contract = Contract::findOrFail($cid);

        $contract->update([
            'status' => 'terminated',
        ]);

        Event::fire(new ContractSaveEvent($contract));

        return response()->json(['data' => $contract]);
    }

    public function addDocuments(Request $request, $tid, $cid) {

        $contract = Contract::findOrFail($cid);
        $files    = $request->files->all();

        $docIds = [];

        foreach ($files as $file) {
            $path = DIRECTORY_SEPARATOR . 'contracts' . DIRECTORY_SEPARATOR . $cid . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
            ];

            $contractDocument = new ContractDocument($params);
            $contract->documents()->save($contractDocument);

            $docIds[] = $contractDocument->id;

            Event::fire(new AwardDocUploadEvent($contractDocument));
        }

        return response()->json(['docIds' => $docIds]);
    }

    public function createPlan(Request $request) {


        $data = $request->get('data');

        $organization = \App\Model\Organization::where('id', $data['procuringEntity']['id'])->first();
        if($organization === null) {
            return response()->json([
                'data' => [
                    'errors' => [
                        'organization not found',
                    ],
                ],
            ]);
        }
        
        $organizationUser = Organization::where('user_id' , '=', Auth::id())->first();
        
        if ($organization->id != $organizationUser->id) {
        	$errorResponse['errors'][0]['organization'] = 'Orzanization of user and procuringEntity didn\'t match';
        	return response()->json($errorResponse);
        }

        $plan = new Plan([
            'description'        => $data['budget']['description'],
            'notes'              => $data['budget']['notes'],
            'procedure_id'       => ProcedureTypes::where('procurement_method', $data['tender']['procurementMethod'])->first()->id,
            'year'               => $data['budget']['year'],
            'code_id'            => Codes::where('code', 'like', $data['classification']['id'])->first()->id,
            'code_kekv_id'       => Codes::where('code', 'like', $data['additionalClassifications'][0]['id'])->first()->id,
            'amount'             => $data['budget']['amount'],
            'currency_id'        => Currencies::where('currency_code', $data['budget']['currency'])->first()->id,
            'start_date'         => $data['tender']['tenderPeriod']['startDate'],
            'amount_net'         => $data['budget']['amountNet'],
            'mode'               => $organization->mode,
        ]);
//        $plan->save();

        $user = Auth::user();
        if($user->organization->id != $organization->id ) {
            return response()->json([
                'data' => [
                    'errors' => [
                        'malformed organization'
                    ]
                ]
            ]);
        }

        $plan = $organization->plans()->save($plan);
        $data['id'] = $plan->id;
        if(isset($data['items'])) {
            foreach ($data['items'] as $k => $itemData) {
                $item                    = new PlanItem([
                    'quantity'    => $itemData['quantity'],
                    'description' => $itemData['description'],
                    'unit_id'     => Units::where('code', 'like', $itemData['unit']['code'])->first()->id,
                ]);
                $item                    = $plan->items()->with('codes')->save($item);
                $data['items'][$k]['id'] = $item->id;
                $codes                   = [];
                $codes[]                 = Codes::where('code', 'like', $data['classification']['id'])->first()->id;
                foreach ($itemData['additionalClassifications'] as $code) {
                    $codes[] = Codes::where('code', 'like', $code['id'])->first()->id;
                }
                $item->codes()->sync($codes);
            }
        }

        Event::fire(new PlanSaveEvent($plan));

        return response()->json(['data' => $data]);
    }

    public function updatePlan(Request $request, $pid) {
        $plan = Plan::findOrFail($pid);
        
        $organizationUser = Organization::where('user_id' , '=', Auth::id())->first();
        
        if ($plan->organization_id != $organizationUser->id) {
        	$errorResponse['errors'][0]['organization'] = 'Orzanization of user and in plan didn\'t match';
        	return response()->json($errorResponse);
        }
        
        $data = $request->get('data');
        $plan->update([
            'description'        => $data['budget']['description'],
            'notes'              => $data['budget']['notes'],
            'procedure_id'       => ProcedureTypes::where('procurement_method', $data['tender']['procurementMethod'])->first()->id,
            'year'               => $data['budget']['year'],
            'code_id'            => Codes::where('code', 'like', $data['classification']['id'])->first()->id,
            'code_kekv_id'       => Codes::where('code', 'like', $data['additionalClassifications'][0]['id'])->first()->id,
            'amount'             => $data['budget']['amount'],
            'currency_id'        => Currencies::where('currency_code', $data['budget']['currency'])->first()->id,
            'start_date'         => $data['tender']['tenderPeriod']['startDate'],
            'amount_net'         => $data['budget']['amountNet'],
        ]);

        if(isset($data['items'])) {
            foreach ($data['items'] as $k => $itemData) {
                if(isset($itemData['id'])) {
                    $item = $plan->items()->find($itemData['id']);
                    $item->update([
                        'quantity'    => $itemData['quantity'],
                        'description' => $itemData['description'],
                        'unit_id'     => Units::where('code', 'like', $itemData['unit']['code'])->first()->id,
                    ]);
                    $data['items'][$k]['id'] = $item->id;
                    $codes                   = [];
                    $codes[]                 = Codes::where('code', 'like', $data['classification']['id'])->first()->id;
                    foreach ($itemData['additionalClassifications'] as $code) {
                        $codes[] = Codes::where('code', 'like', $code['id'])->first()->id;
                    }
                    $item->codes()->sync($codes);
                } else {
                    $item                    = new PlanItem([
                        'quantity'    => $itemData['quantity'],
                        'description' => $itemData['description'],
                        'unit_id'     => Units::where('code', 'like', $itemData['unit']['code'])->first()->id,
                    ]);
                    $item                    = $plan->items()->with('codes')->save($item);
                    $data['items'][$k]['id'] = $item->id;
                    $codes                   = [];
                    $codes[]                 = Codes::where('code', 'like', $data['classification']['id'])->first()->id;
                    foreach ($itemData['additionalClassifications'] as $code) {
                        $codes[] = Codes::where('code', 'like', $code['id'])->first()->id;
                    }
                    $item->codes()->sync($codes);
                }
            }
        }
        Event::fire(new PlanSaveEvent($plan));
        return response()->json(['data' => $data]);
    }

    public function getCbdContract(Request $request, $cid) {
        $contract = Contract::find($cid);
        $errors = [];
        if($contract->status == 'pending') {
            $errors[] = 'contract is pending';
            return response()->json(['data' => [], 'errors' =>  $errors]);
        }

        $api = new Api();
        $api->namespace = 'contracts';
        $response = $api->get($contract->cbd_id);

        $cbd_ids = [];
        foreach($response['data']['documents'] as $document) { //собираем айди
            $cbd_ids[][$document['id']] = (integer)ContractDocuments::where('orig_id', $document['id'])->first()->id;
        }

        //dd($cbd_ids);
        $amountOfDocInArray = count($response['data']['documents']);
        for($i =0; $i < $amountOfDocInArray; $i++) {
            //dd($cbd_ids);//$response['data']['documents'][$i]['id'];
            $response['data']['documents'][$i]['id'] = $cbd_ids[$i][$response['data']['documents'][$i]['id']];
        }


        $cbd_changes_id = [];
        if (isset($response['data']['changes'])) {
        	foreach($response['data']['changes'] as $change) { //собираем айди
            	$cbd_changes_id[][$change['id']] = (integer)ContractChange::where('cbd_id', $change['id'])->first()->id;
            	//dd($cbd_changes_id);
        	}
        }
        $changesCount = count($cbd_changes_id);
        for($i =0; $i < $changesCount; $i++) {
            $response['data']['changes'][$i]['id'] = $cbd_changes_id[$i][$response['data']['changes'][$i]['id']];
        }

        return $response;
    }

    public function getUserById(Request $request, $user_id ) {

        $user = User::find($user_id);

        if($user == null) {
            return response()->json(['data' => '', 'errors' => ['user not found']]);
        }

        return response()->json(['data' => [
            'source'                 => $user->organization->source,
            'kind_id'                => $user->organization->kind_id,
            'name'                   => $user->organization->name,
            'confirmed'              => $user->organization->confirmed,
            'legal_name_en'          => '',
            'name_en'                => '',
            'type'                   => $user->organization->type,
            'identifier'             => $user->organization->identifier,
            'mode'                   => $user->organization->mode,
            'country_id'             => 1,
            'region_id'              => $user->organization->region_id,
            'region_name'            => $user->organization->region_name,
            'postal_code'            => $user->organization->postal_code,
            'locality'               => $user->organization->locality,
            'street_address'         => $user->organization->street_address,
            'contact_name'           => $user->organization->contact_name,
            'contact_name_en'        => '',
            'contact_email'          => $user->organization->contact_email,
            'contact_available_lang' => '',
            'contact_phone'          => $user->organization->contact_phone,
            'contact_fax'            => '',
            'contact_url'            => $user->organization->contact_url,
            'sign'                   => $user->organization->sign,
            'regname'                => $user->name
        ]]);
    }

    public function createUserWithOrg(Request $request) {
        $data = $request->all();

        $var6 = json_encode($data, JSON_UNESCAPED_UNICODE);

        $isExist = User::where('email', $data['regemail'])->first();

        if($isExist != null) {
            return response()->json(['data' => ['id' => '', 'user_id' => ''], 'errors' => ['user is already exists']]);
        }

        $user = new User([
            'email'    => $data['regemail'],
            'name'     => $data['regname'],
            'password' => $data['regpassword'],
            'active'   => 1
        ]);

        $user->save();

        $region = TendersRegions::where('id', $data['address']['UTregionid'])->first();

        $organization = new Organization([
            'source'                 => 2,
            'kind_id'                => Kind::where('kind', 'like', $data['kind'])->first()->id,
            'name'                   => $data['name'],
            'legal_name_en'          => '',
            'name_en'                => '',
            'type'                   => 'customer',
            'identifier'             => $data['identifier']['id'],
            'mode'                   => 0,
            'country_id'             => 1,
            'region_id'              => $region->id,
            'region_name'            => $region->region_ua,
            'postal_code'            => $data['address']['postalCode'],
            'locality'               => $data['address']['locality'],
            'street_address'         => $data['address']['streetAddress'],
            'contact_name'           => $data['contactPoint']['name'],
            'contact_name_en'        => '',
            'contact_email'          => $data['contactPoint']['email'],
            'contact_available_lang' => '',
            'contact_phone'          => $data['contactPoint']['telephone'],
            'contact_fax'            => '',
            //'contact_url'            => $data['identifier']['url'],
            'sign'                   => $data['sign'], //TODO добавить миграцию
            'signed_json'            => json_encode($data)//TODO добавить миграцию
        ]);


        $organization = $user->organization()->save($organization);

        Mail::queue('emails.support', compact('organization'), function($message) {
            $message->to('support@zakupki.com.ua', 'Support')->subject('Новый користувач від ПАРУСА');
        });

        return response()->json(['data' => ['id' => $organization->id, 'user_id' => $user->id]]);
    }

    public function updateOrganization(Request $request, $organization_id) {
        $organization = Auth::user()->organization;
        $data = $request->all();

        if($organization->id != $organization_id) {
            return response()->json([
                'data' => [
                    'errors' => [
                        'malformed organization',
                    ],
                ],
            ]);
        }

        if($organization->identifier != $data['identifier']['id']) {
            return response()->json([
                'data' => [
                    'errors' => [
                        'you are not allowed to change identifier',
                    ],
                ],
            ]);
        }

        $region = TendersRegions::where('id', $data['address']['UTregionid'])->first();

        $organization->update([
            'source'                 => 2,
            'kind_id'                => Kind::where('kind', 'like', $request['kind'])->first()->id,
            'name'                   => $data['name'],
            'legal_name_en'          => '',
            'name_en'                => '',
            'type'                   => 'customer',
            'identifier'             => $data['identifier']['id'],
            'mode'                   => 0,
            'country_id'             => 1,
            'region_id'              => $region->id,
            'region_name'            => $region->region_ua,
            'postal_code'            => $data['address']['postalCode'],
            'locality'               => $data['address']['locality'],
            'street_address'         => $data['address']['streetAddress'],
            'contact_name'           => $data['contactPoint']['name'],
            'contact_name_en'        => '',
            'contact_email'          => $data['contactPoint']['email'],
            'contact_available_lang' => '',
            'contact_phone'          => $data['contactPoint']['telephone'],
            'contact_fax'            => '',
            //'contact_url'            => $data['identifier']['url'],
            'sign'                   => $data['sign'], //TODO добавить миграцию
            'signed_json'            => json_encode($data)//TODO добавить миграцию
        ]);

        return response()->json([
            'data' => [
                'status' => 'updated',
                'errors' => []
            ],
        ]);
    }

    public function getPlanById(Request $request, $id) {

        try {
            $plan = Plan::where('id', $id)->first();
            $api = new Api();
            $api->namespace = 'plans';
            return  $api->get($plan->cbd_id);
        } catch (\Exception $e ) {
            return ['data'=>[], 'errors' => ['planSyncError' => 'plan is not synced yet']];
        }
    }

    public function getTenderByCbdId(Request $request, $id) {
        $tender = Tender::where('id', $id)->first();
        $api = new Api();
        $api->namespace = 'tenders';

        return $api->get($tender->cbd_id);
    }

    public function createChange(Request $request, $contract_id) {
        $contract = Contract::find($contract_id);

        $data = $request->get('data');

        $change = new ContractChange([
            'status' => 'pending',
        	'tender_id' => Contract::where('id','like',$contract_id)->first()->tender_id,
            'rationale' => $data['rationale'],
            'rationale_type_id' => RationaleType::where('name', 'like', $data['rationaleTypes'][0])->first()->id,
        ]);
        $contract->changes()->save($change);

        Event::fire(new ContractSaveEvent($contract));

        return ['data' => ['ids' => $change->id]];
    }

    public function getChange(Request $request, $contract_id, $change_id) {
        $change = Contract::find($contract_id)->changes()->find($change_id);

        $api = new Api();
        $api->namespace = 'contracts';

        return $api->get($change->contract->cbd_id, 'changes/'.$change->cbd_id);
    }

    public function addDocumentToChange(Request $request, $contractId) {
        $contract = Contract::findOrFail($contractId);
        $files    = $request->files->all();

        $docIds = [];

        foreach ($files as $file) {
            $path = DIRECTORY_SEPARATOR . 'contracts' . DIRECTORY_SEPARATOR . $contractId . DIRECTORY_SEPARATOR . time() . '_' . $file->getClientOriginalName();
            Storage::disk('documents')->put($path, File::get($file));

            $params = [
                'document_parent_id' => 0,
                'title'              => $file->getClientOriginalName(),
                'description'        => $request->get('description') !== null ? $request->get('description') : '',
                'format'             => $file->getClientOriginalExtension(),
                'path'               => $path,
                'type_id'           => $request->get('type_id'),
            ];

            $contractDocument = new ContractDocuments($params);
            $contract->documents()->save($contractDocument);

            $docIds[] = $contractDocument->id;

            Event::fire(new ContractDocUploadEvent($contractDocument));
        }

        return response()->json(['ids' => $docIds]);
    }

    public function updateChangeDocument(Request $request, $cid, $did) {
        $change         = ContractChange::findOrFail($request->all()['data']['relatedItem']);
        $contract       = Contract::findOrFail($cid);
        $changeDocument = ContractDocuments::findOrFail($did);

        Event::fire(new PatchChangeDocumentEvent($contract, $changeDocument, $change));

        return response()->json(['status' => 'updated']);
    }

    public function changeContract(Request $request, $cid) {
        $contract = Contract::find($cid);

        $data = $request->all();

        //title, description, status, value.amount, period, items, amountPaid.amount, terminationDetails. поля которые можно модифицировать

        $contract->title               = !empty($data['data']['title']) ? $data['data']['title'] : $contract->title;
        $contract->amount               = !empty($data['data']['value']['amount']) ? $data['data']['value']['amount'] : $contract->amount;
        $contract->description         = !empty($data['data']['description']) ? $data['data']['description'] : $contract->description;
        $contract->amount_paid         = !empty($data['data']['amountPaid']['amount']) ? $data['data']['amountPaid']['amount'] : $contract->amount_paid;
        $contract->period_date_end     = !empty($data['data']['period']['endDate']) ? $data['data']['period']['endDate'] : $contract->period_date_end;
        $contract->period_date_start   = !empty($data['data']['period']['startDate']) ? $data['data']['period']['startDate'] : $contract->period_date_start;
        $contract->termination_details = !empty($data['data']['terminationDetails']) ? $data['data']['terminationDetails'] : $contract->termination_details;

        $contract->save();

        Event::fire(new ContractSaveEvent($contract));

        return response()->json(['status' => 'updated']);
    }

    public function activateChange(Request $request, $contract_id, $change_id) {
        $change = Contract::find($contract_id)->changes()->find($change_id);

        $change->status = 'activate';
        $change->save();

        Event::fire(new ContractSaveEvent($change->contract));

        return response()->json(['status' => 'activated']);
    }

    public function runQueue() {
        if(env('APP_ENV') == 'herak') {
            exec('php artisan queue:listen');
            exec('php artisan queue:listen tenders');
        }
    }

    public function changeOrganizationMode(Request $request, $organizationId) {
        $organization = Auth::user()->organization;

        if(!$organization->isConfirmedApi()) {
            return response()->json([
                'data' => [
                    'errors' => [
                        'organization is not confirmed yet',
                    ],
                ],
            ]);
        }

        if($organization->id != $organizationId) {
            return response()->json([
                'data' => [
                    'errors' => [
                        'malformed organization',
                    ],
                ],
            ]);
        }

        $organization->mode = $request->get('mode');
        $organization->save();

        return response()->json(['data' => ['mode' => $organization->mode]]);
    }

    private function _getTenderData($data, $source) {

        if($data['procurementMethod'] === 'limited') {
            $data['value']['amount'] = $data['lots'][0]['value']['amount'];
        }

        $tenderData = [
            'procurement_type_id' => isset($data['procurement_type_id']) ? $data['procurement_type_id']:0,
            'type_id'             => isset($data['type_id']) ? $data['type_id'] : 1,
            'multilot'            => $data['procurementMethod'] === 'open' ? 1 : 0,
            'title'               => $data['title'],
            'description'         => isset($data['description']) ? $data['description'] : '',
            'tenderID'            => isset($data['tenderID']) ? $data['tenderID'] : '',
            'cbd_id'              => isset($data['id']) ? $data['id'] : '',
            'source'              => $source,
            'mode'                => isset($data['mode']) && $data['mode'] == 'test' ? 0 : 1,
            'status'              => isset($data['status']) ? $data['status'] : 'draft',
            'number_of_bids'      => isset($data['numberOfBids']) ? $data['numberOfBids'] : 0,
            'auction_url'         => isset($data['auctionUrl']) ? $data['auctionUrl'] : '',
            'amount'              => isset($data['value']['amount']) ? $data['value']['amount'] : '',
            'currency_id'         => \App\Model\Currencies::where('currency_code', 'UAH')->first()->id,
            'tax_included'        => isset($data['value']['valueAddedTaxIncluded']) ? $data['value']['valueAddedTaxIncluded'] : '',
            'minimal_step'        => isset($data['minimalStep']['amount']) ? $data['minimalStep']['amount'] : 0,
            'auction_start_date'  => isset($data['auctionPeriod']['startDate']) ? $data['auctionPeriod']['startDate'] : null,
            'auction_end_date'    => isset($data['auctionPeriod']['endDate']) ? $data['auctionPeriod']['endDate'] : null,
            'award_start_date'    => isset($data['awardPeriod']['startDate']) ? $data['awardPeriod']['startDate'] : null,
            'award_end_date'      => isset($data['awardPeriod']['endDate']) ? $data['awardPeriod']['endDate'] : null,
            'enquiry_start_date'  => isset($data['enquiryPeriod']['startDate']) ? $data['enquiryPeriod']['startDate'] : null,
            'enquiry_end_date'    => isset($data['enquiryPeriod']['endDate']) ? $data['enquiryPeriod']['endDate'] : '',
            'tender_start_date'   => isset($data['tenderPeriod']['startDate']) ? $data['tenderPeriod']['startDate'] : null,
            'tender_end_date'     => isset($data['tenderPeriod']['endDate']) ? $data['tenderPeriod']['endDate'] : '',
            'date_modified'       => isset($data['dateModified']) ? $data['dateModified'] : null,
            'contact_phone'       => $data['procuringEntity']['contactPoint']['telephone'],
            'contact_name'        => $data['procuringEntity']['contactPoint']['name'],
            'contact_email'       => isset($data['procuringEntity']['contactPoint']['email']) ? $data['procuringEntity']['contactPoint']['email'] : '',
        ];

        return $tenderData;
    }
}
