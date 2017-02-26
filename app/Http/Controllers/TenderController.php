<?php

namespace App\Http\Controllers;

use App\Api\Api;
use App\Jobs\ChangeStatus;
use App\Jobs\GetTenderCredentials;
use App\Model\TenderContacts;
use App\Services\FilterService\FilterService;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use App\Services\ValidationService\ValidationService;
use Maatwebsite\Excel\Facades\Excel;
use sngrl\SphinxSearch\SphinxSearch;
use SoapBox\Formatter\Formatter;
use App\Events\TenderSaveEvent;
use App\Events\TenderUpdateEvent;
use App\Http\Requests;
use App\Http\Requests\CreateTenderRequest;
use App\Jobs\SyncTender;
use App\Model\Bid;
use App\Model\Classifiers;
use App\Model\Codes;
use App\Model\TenderContact;
use App\Model\Contract;
use App\Model\Currencies;
use App\Model\Document;
use App\Model\DocumentType;
use App\Model\Feature;
use App\Model\FeatureValue;
use App\Model\Guarantee;
use App\Model\Item;
use App\Model\Languages;
use App\Model\Lot;
use App\Model\Notification;
use App\Model\ProcurementType;
use App\Model\Status;
use App\Model\Tender;
use App\Model\TendersRegions;
use App\Model\Units;
use App\Model\User;
use App\NegotiationCause;
use App\Model\ProcedureTypes;
use Carbon\Carbon;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Validator;


class TenderController extends Controller
{
    const DEFAULT_LANGUAGE = 'ua';

    protected function _saveFeatures($entity, $data, $tenderId)
    {
        $featureIds = [];
        if (!empty($data['features'])) {
            foreach ($data['features'] as $featureData) {
                if (isset($featureData['id']) && !empty($featureData['id'])) {
                    $feature = $entity->features->find($featureData['id']);
                    $feature->update($featureData);
                } else {
                    $featureData['tender_id'] = $tenderId;
                    $feature = new Feature($featureData);
                    $entity->features()->save($feature);
                }
                $featureValuesIds = [];
                foreach ($featureData['values'] as $featureValueData) {
                    if (isset($featureValueData['id']) && !empty($featureValueData['id'])) {
                        $featureValue = $feature->values()->find($featureValueData['id']);
                        $featureValue->update($featureValueData);
                    } else {
                        $featureValue = new FeatureValue($featureValueData);
                        $feature->values()->save($featureValue);
                    }
                    $featureValuesIds[] = $featureValue->id;
                }
                $featureIds[] = $feature->id;
                $feature->values()->whereNotIn('id', $featureValuesIds)->delete();
            }
        }
        $entity->features()->whereNotIn('id', $featureIds)->delete();
    }

    protected function _uploadDocs($entity, $files, $path, $docTypes, $isNew = false)
    {
        if (is_array($files)) foreach ($files as $index => $file) {
            if ($file) {
                Storage::disk('documents')->put($path . $file->getClientOriginalName(), File::get($file));
                $params = [
                    'title' => '',
                    'description' => '',
                    'format' => '',
                    'type_id' => isset($docTypes[$index]) ? $docTypes[$index] : 0,
                    'path' => $path . $file->getClientOriginalName(),
                    'tender_id' => $entity->tender->id
                ];


                if ($isNew) {
                    $oldDoc = Document::find($index);
                    $entity = $oldDoc->documentable;
                    $params['type_id'] = $oldDoc->type_id;
                    $params['orig_id'] = $oldDoc->orig_id;
                    $params['tender_id'] = $oldDoc->tender_id;
                    $oldDoc->status = 'old';
                    $oldDoc->save();
                } else {
                    $params['type_id'] = isset($docTypes[$index]) ? $docTypes[$index] : 0;
                }

                $newDoc = new Document($params);

                $entity->documents()->save($newDoc);

            }
        }
    }

    protected function _getFeaturesAmount($data)
    {
        $tenderFeatureSum = array_sum($this->getMaxTenderFeature(array_key_exists('features', $data) ? $data['features'] : null));

        $lotsFeature = $this->getMaxLotFeature($data['lots']);

        $totalSum = []; //tender + lot + items inside the lot
        foreach ($lotsFeature as $key => $lotFeatureSum) {
            $totalSum[] = ['lot_id' => $key + 1, 'tender_feature_sum' => (string)($tenderFeatureSum + (int)$lotFeatureSum)];
        }
//        $amount = 0;
//        foreach ($data as $key => $value) {
//            if ($key === 'features') {
//                foreach ($data['features'] as $featureData) {
//                    foreach ($featureData['values'] as $valueData) {
//                        $amount += $valueData['value'];
//                    }
//                }
//            } elseif (is_array($value)) {
//                $amount += $this->_getFeaturesAmount($value);
//            }
//        }
        return $totalSum;
    }

    /**
     * Returns sum of feature per each lot including lot features
     * and items feature inside the lot
     *
     * @param $data
     * @return array
     */
    private function getMaxLotFeature($data)
    {
        $featureMax = [];

        foreach ($data as $lot) {
            $lotFeature = [];
            $itemFeature = [];
            if (array_key_exists('features', $lot)) {
                foreach ($lot['features'] as $feature) {
                    $max = 0;
                    foreach ($feature['values'] as $featureValue) {
                        if ($featureValue['value'] > $max) {
                            $max = $featureValue['value'];
                        }
                    }
                    $lotFeature[] = $max;
                }
            }

            foreach ($lot['items'] as $item) {
                if (array_key_exists('features', $item)) {
                    foreach ($item['features'] as $feature) {
                        $max = 0;
                        foreach ($feature['values'] as $value) {
                            if ($value['value'] > $max) {
                                $max = $value['value'];
                            }
                        }
                        $itemFeature[] = $max;
                    }
                }
            }


            $sum = array_sum(array_merge($lotFeature, $itemFeature)); //sum of lot features and item features
            $featureMax[] = $sum;
        }
        return $featureMax;
    }

    private function getMaxItemFeature($data)
    {
        $featureMax = [];
        foreach ($data as $lot) {
            foreach ($lot['items'] as $item) {
                foreach ($item['features'] as $feature) {
                    $max = 0;
                    foreach ($feature['values'] as $value) {
                        if ($value['value'] > $max) {
                            $max = $value['value'];
                        }
                    }
                    $featureMax[] = $max;
                }
            }
        }
        return $featureMax;
    }

    /**
     * Calculate the max value tender feature and add it to array or return null if arra
     *
     * @param $data
     * @return array|int
     */
    private function getMaxTenderFeature($data)
    {
        $featuresMax = [];
        if (is_array($data) && !is_null($data)) {
            foreach ($data as $key => $value) { //показник
                $max = 0;
                foreach ($value['values'] as $item) {//опции показника
                    if ($item['value'] > $max) {
                        $max = $item['value'];
                    }
                }
                $featuresMax[] = $max;
            }
            return $featuresMax;
        }
        return [];
    }

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

    protected function _validateCodes($tenderData, $charNum = 3)
    {
        $codesIds = $this->_getItemsCodes($tenderData);
        $codes = Codes::whereIn('id', $codesIds)->get();
        $cpv = $dkpp = $cpvAll = $dkppAll = [];
        foreach ($codes as $code) {
            $add = false;
            if ($code->type == 1) {
                $currentCode = substr($code->code, 0, $charNum);
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

    protected function _validateTender($tenderData, $tender = null)
    {
        if (!empty($tenderData['amount'])) {
            $amount = $tenderData['amount'];
        } else {
            $amount = 0;
            foreach ($tenderData['lots'] as $lot) {
                $amount += (int) $lot['amount'];
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
            'documentsCount'     => 'in:1',
        ];

        $tenderID = (isset($tenderData['tender_id'])) ? intval($tenderData['tender_id']) : null;
        if ($tenderData['type_id'] == 1 && !$tenderID) {
            if ($amount < 50000) {
                $tenderDuration = $enquiryDuration = 2;
            } else {
                $enquiryDuration = 4;
                $tenderDuration = 3;
            }

            $minEnquiryEndDate = getDateWithWorkdays(time(), $enquiryDuration);
            if ($minEnquiryEndDate < strtotime($tenderData['enquiry_end_date'])) {
                $rules['tender_start_date'] = 'required|date|after:enquiry_end_date';
                $rules['tender_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', getDateWithWorkdays(strtotime($tenderData['enquiry_end_date']), $tenderDuration));
            } else {
                $rules['enquiry_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', $minEnquiryEndDate);
            }
        }

        $messages = [
            'contact_phone.regex' => 'Введіть, будь ласка, Ваш номер телефону в форматі +380998877665',
            'contact_phone.required' => 'Поле "Телефон" необхідно заповнити',
            'amount.min' => "Очікувана вартість закупівлі не може бути меншою, ніж 3000 грн.",
            'amount.numeric' => 'Поле "Бюджет лоту" повинно бути числом',
            'type_id.in' => "Шановний користувач, на нашому майданчику на даний момент доступні лише допорогові закупівлі",
            //'identifier.required'    => "Поле \"Код ЄДРПОУ\" необхідно заповнити",
            'contact_email.required' => 'Поле "Email" необхідно заповнити',
            'contact_email.email' => 'Введіть, будь ласка, Вашу електронну пошту в форматі priklad@domain.com',
            'documentsCount.in'      => 'Для створення закупівлі додайте документи та натисніть Створити',
        ];

        if (($tender && $tender->hasOneClassifier()) || (!$tender && time() > strtotime(env('ONE_CLASSIFIER_FROM')))) {
            $messages['cpv_codes_count.in'] = 'У всіх лотах мають співпадати перші чотири символи коду класифікатора ДК 021:2015';
            $messages['dkpp_codes_count.in'] = 'У всіх лотах мають співпадати перші сім символів коду додаткового класифікатора';
        } else {
            $messages['cpv_codes_count.in'] = 'Оберіть, будь ласка, код класифікатору зі списку.';
            $messages['dkpp_codes_count.in'] = 'У всіх лотах мають співпадати перші п\'ять символів коду класифікатора ДК 016:2010 і перші три символи коду класифікатора ДК 021:2015';
        }

        if ($tenderData['type_id'] == 1 && !$tenderID) {
            $messages['enquiryDifference.min'] = 'Період уточнень повинен бути не менше, ніж ' . $enquiryDuration . ' ' . Lang::choice('день | дні | днів', $enquiryDuration, [], 'ru');
            $messages['tenderDifference.min'] = 'Період подачі пропозицій повинен бути не менше, ніж ' . $tenderDuration . ' ' . Lang::choice('день | дні | днів', $tenderDuration, [], 'ru');
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
            $minDateEndTimestamp = $currentDate + $minDayEnd * 86400 / env('API_ACCELERATOR');
            if ($tenderID) {
                $dateToEndTender = time() + 7 * 86400 / env('API_ACCELERATOR');
                if ($dateToEndTender > $minDateEndTimestamp)
                    $minDateEndTimestamp = $dateToEndTender;
            }
            $minDateEnd = Carbon::createFromTimestamp($minDateEndTimestamp);
            $rules['tender_end_date'] = 'required|date|after:' . $minDateEnd;
            $messages['tender_end_date.required'] = 'Поле "Закінчення періоду подачі пропозицій" необхідно заповнити';
            $messages['tender_end_date.min'] = 'Період подачі пропозицій повинен бути не раніше, ніж ' . $minDayEnd;
            $messages['tender_end_date'] = 'Період подачі пропозицій повинен бути не раніше, ніж ' . $minDayEnd;
        }

        if ($tenderData['type_id'] == 3 || $tenderData['type_id'] == 10) {
            $rules['title_en'] = 'required';
            $messages['title_en.required'] = "Поле \"Назва англійсьокю\" повино бути заповнене.";
        }
        if ($tenderData['type_id'] == 8) {
            if ($tenderID) {
                $tender = Tender::findOrFail($tenderID);
                $tenderStartDate = strtotime($tender->tender_start_date);
                $daysFromStartToEnd = getWorkdaysBetweenDates($tenderStartDate, getDateWithWorkdays(time(), 3));
                $minDayEnd = 3;
                if ($daysFromStartToEnd < 6)
                    $minDayEnd = 6;
            } else {
                $minDayEnd = 6;
            }
            $minTimeTxt = date('Y-m-d H:i:s', getDateWithWorkdays(time(), $minDayEnd));
            $rules['tender_end_date'] = 'required|date|after:' . $minTimeTxt;
            $messages['tender_end_date.required'] = 'Поле "Закінчення періоду подачі пропозицій" необхідно заповнити';
            $messages['tender_end_date.after'] = 'Кінцевий строк подання тендерних пропозицій повинен бути не раніше, ніж ' . $minTimeTxt;
            $messages['tender_end_date'] = 'Кінцевий строк подання тендерних пропозицій повинен бути не раніше, ніж ' . $minTimeTxt;
        }

        if (($tender && $tender->hasOneClassifier()) || (!$tender && time() > strtotime(env('ONE_CLASSIFIER_FROM')))) {
            $tenderData = $this->_validateCodes($tenderData, 4);
            $this->_validateCodes($tenderData, 4);
        } else {
            $tenderData = $this->_validateCodes($tenderData);
            $this->_validateCodes($tenderData);
        }
        $tenderData['features_amount'] = $this->_getFeaturesAmount($tenderData);


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

        foreach ($tenderData['features_amount'] as $lotAmount) {
            $validator = Validator::make(['tender_feature_sum' => $lotAmount['tender_feature_sum']], $featureRules, [
                'tender_feature_sum.min' => 'Додаткові нецінові показники не можуть бути менше нуля',
                'tender_feature_sum.max' => 'Додаткові нецінові показники в сумі не можуть перевищувати ' . $maxFeatureSum . '% (перевищення в лоті ' . $lotAmount['lot_id'] . ')',
            ]);

            if ($validator->fails()) {
                return $validator;
            }
        }


        return Validator::make($tenderData, $rules, $messages);
    }

    protected function _validateLot($lotData, $index, $procurementTypeId, $tenderTypeId = 0)
    {
        $index++;
        $lotData['items_count'] = isset($lotData['items']) ? count($lotData['items']) : 0;
        if ($procurementTypeId == 1) {
            $minStep = (int)$lotData['amount'] * 0.5 / 100;
            $maxStep = (int)$lotData['amount'] * 3 / 100;
        } else {
            $minStep = 0;
            $maxStep = (int)$lotData['amount'];
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

    protected function _validateItem($itemData, $itemIndex, $lotIndex, $tenderTypeId = 0, $tender = null)
    {
        $itemIndex++;
        $lotIndex++;
        $notSpecifiedCodeId = ($code = Codes::where('code', '99999999-9')->first()) ? $code->id : 0;
        $additionalClassifierIndex = 0;
        foreach ($itemData['codes'] as $index => $code) {
            $codes[] = $code['id'];
            if ($index != 1)
                $additionalClassifierIndex = $index;
        }

        $rules = [
            'quantity' => 'required|integer',
            'unit_id' => 'required',
            'delivery_date_start' => 'date',
            'delivery_date_end' => 'date|after:delivery_date_start',
            'cpv'    => 'required',
            'codes.1.id' => 'required',
        ];

        if ($tenderTypeId != 9 && $tenderTypeId != 10) {
            $rules['delivery_date_start'] = 'date';
            $rules['delivery_date_end'] = 'date|after:delivery_date_start';
        }

        $descriptions = [
            'quantity' => 'Кількість',
            'unit_id' => 'Одиниця виміру',
            'delivery_date_start' => 'Початок періоду поставки',
            'delivery_date_end' => 'Кінець періоду поставки',
            'cpv' => 'Код класифікатора CPV',
            'codes.1.id' => 'Код CPV',
        ];

        if (($tender && $tender->hasOneClassifier()) || (!$tender && time() > strtotime(env('ONE_CLASSIFIER_FROM')))) {
            if ($itemData['codes'][1]['id'] == $notSpecifiedCodeId) {
                $rules['dkpp'] = 'required';
                $rules['codes.' . $additionalClassifierIndex . '.id'] = 'required';
                $messages['dkpp.required'] = 'Введіть код додаткового класифікатора';
                $messages['codes.' . $additionalClassifierIndex . '.id.required'] = 'Виберіть код додаткового класифікатора з випадаючого списку';
            }
        } else {
            if (array_key_exists('additionalClassifier', $itemData)) {
                $rules['additionalClassifier'] = 'required';
                $rules['codes.' . intval($itemData['additionalClassifier']) . '.id'] = 'required';
                $descriptions['additionalClassifier'] = 'Додатковий класифікатор';
                $messages['codes.' . intval($itemData['additionalClassifier']) . '.id.required'] = "Лот №$lotIndex, товар №$itemIndex. Додатковий класифікатор необхідно вибрати зі списку. Введіть в поле перші цифри коду для отримання підказки";
            }

            $rules['dkpp'] = 'required';
            $rules['codes.' . $additionalClassifierIndex . '.id'] = 'required';
            $messages['dkpp.required'] = 'Введіть код додаткового класифікатора';
            $messages['codes.' . $additionalClassifierIndex . '.id.required'] = 'Виберіть код додаткового класифікатора з випадаючого списку';
        }

        if ($itemData['region_id'] != (int)0) {
            $rules['postal_code'] = 'required|digits_between:5,5';
            $rules['locality'] = 'required';
            $rules['delivery_address'] = 'required';

            $descriptions['postal_code'] = 'Індекс';
            $descriptions['locality'] = 'Місто';
            $descriptions['delivery_address'] = 'Адреса';
        }

        $messages['cpv.required'] = "У всіх лотах мають співпадати перші чотири символи коду класифікатора ДК 021:2015";
        $messages['quantity.integer'] = "Лот №$lotIndex, товар №$itemIndex. Поле \":attribute\" повинно бути цілим числом.";
        $messages['codes.1.id.required'] = "Лот №$lotIndex, товар №$itemIndex. :attribute необхідно вибрати зі списку. Введіть в поле перші цифри коду для отримання підказки";

        if ($tenderTypeId != 4 && $tenderTypeId != 5 && $tenderTypeId != 6 && $itemData['delivery_date_start'] != '') {
            $rules['delivery_date_start'] .= '|after:' . date('d.m.Y');
            $messages['delivery_date_start.after'] = 'Початок періоду поставки повинен бути не раніше ніж ' . date('d.m.Y', time(date('d.m.Y')) + 86400);
        }

        $this->_validateFeature($itemData);

        return Validator::make($itemData, $rules, $messages, $descriptions);
    }

    protected function _validate($data, $tender = null)
    {
        $validate = $this->_validateTender($data, $tender);
        if (!$validate->fails()) {
            $procedureType = ProcedureTypes::find($data['type_id']);
            foreach ($data['lots'] as $lotIndex => $lot) {
                if (isset($procedureType) && $procedureType->procurement_method == 'open') { //procurement_method = open
                    $validate = $this->_validateLot($lot, $lotIndex, array_key_exists('procurement_type_id', $data) ? $data['procurement_type_id'] : false, $data['type_id']);
                    if (!$validate->fails()) {
                        foreach ($lot['items'] as $itemIndex => $item) {
                            $validate = $this->_validateItem($item, $itemIndex, $lotIndex, $data['type_id'], $tender);
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

//                                foreach($fut['values'] as $vals) {
//                                    if ($vals['value'] != 0) {
//                                        $checkVar[$x] = 'Товар №' . $countrItems . ' Лот №' . $countrLots;
//                                        $x++;
//                                    } else {
//                                        unset($checkVar[$x]);
//                                        break;
//                                    }
//                                }
//                                $countrItems++;
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


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $listName = 'tenders-list';
        $filters = $this->_createFilter($listName, '/tender/filter', 'tender');

        $user = Auth::user();
        $tenders = $user->tenders()->withoutDeleted()->orderBy('created_at', 'DESC')->paginate(20);

        return view('pages.tender.user_list', compact('tenders','filters','listName'));
    }

    /**
     * @param string $name
     * @param string $url
     * @param string $table
     * @return string
     */
    private function _createFilter($name, $url, $table)
    {
        $procedure = new ProcedureTypes();
        $statuses = new Status();
        $filterService = new FilterService($table);

        $filterService->setTextField($table, 'tenderID','ID');
        $filterService->setTextField($table, 'auction_start_date', 'Дата аукціону', FilterService::DATE_LIKE_TYPE);
        $filterService->setTextField('lot', 'auction_start_date', 'Дата лоту', FilterService::DATE_LIKE_TYPE);
        $filterService->setTextField($table, 'title', 'Найменування', FilterService::TEXT_TYPE);
        $filterService->setListField($table, 'type_id', 'Процедура', FilterService::TEXT_TYPE,
            $procedure->getAllProceduresArray());
        $filterService->setListField($table, 'status', 'Статус', FilterService::TEXT_TYPE,
            $statuses->getAllStatuses('tender'));

        return $filterService->create($name, $url);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filter(Request $request)
    {
        if($request->ajax()) {
            $service = new FilterService('tender');
            $model = $service->createFilterRequest(Auth::user()->tenders(), $request->all());
            $tenders = $model->withoutDeleted()->orderBy('created_at', 'DESC')->paginate(20);
            return view('pages.tender._part.list', compact('tenders'));
        }
    }

    /**
     * @param Request $request
     * @param Classifiers $classifier
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listTenders(Request $request, Classifiers $classifier)
    {
        $sphinx = new SphinxSearch();
        $classifiers    = $classifier->getNewClassifiersList();
        $regions = TendersRegions::where('status', 1)->lists('region_ua', 'id');
        $search = $request->get('search');


        if (!Auth::user()->organization || Auth::user()->organization->mode == 0) {
            $mode = 0;
        } else {
            $mode = 1;
        }
        if (preg_match("/(UA-\d\d\d\d-\d\d-\d\d-\d{6})/", $search['s'])) {
            $tender = Tender::where('tenderID', $search['s'])->first();
            if ($tender) {

                return redirect()->route('tender.show', $tender->id);
            }
        }
        if (!empty($search['s'])) {
            $searchString = '"'.$search['s'].'"/1';
        } else {
            $searchString = '';
        }
        $index = empty(env('SPHINX_INDEX')) ? 'tenders' : env('SPHINX_INDEX');
        $results = $sphinx->search($searchString, $index)
            ->limit(1000)
            ->filter('mode', $mode)
            ->setMatchMode(\Sphinx\SphinxClient::SPH_MATCH_EXTENDED);
        if (!empty($searchString)) {
            $results = $results->setSortMode(\Sphinx\SphinxClient::SPH_SORT_EXTENDED, "@weight DESC");
        } else {
            $results = $results->setSortMode(\Sphinx\SphinxClient::SPH_SORT_EXTENDED, "date_modified DESC");
        }

        if (!empty($search['status'])) {
            $results = $results->filter('status_id', $search['status']);
        }
        if (!empty($search['code_id'])) {
            $results = $results->filter('code_id', $search['code_id']);
        }
        if (!empty($search['region'])) {
            $results = $results->filter('region_id', $search['region']);
        }
        $results = $results->get();
        foreach ($results as $result) {
            $ids[] = $result->id;
        }
        $tenders = Tender::with('organization')
            ->with('currency')
            ->with('statusDesc')
            ->withoutDeleted();
        if (!empty($ids)) {
            $tenders = $tenders->whereIn('tenders.id', $ids)
                ->orderByRaw('FIELD(tenders.id, '.implode(', ', $ids).')')
                ->paginate(20);
        } else {
            $tenders = null;
        }

        $statuses = Status::where('namespace', 'tender')->whereNotIn('status', ['draft', 'published', 'deleted', 'draft.stage2'])->get();

        return view('pages.tender.lists', compact('tenders', 'statuses','classifiers', 'search', 'regions'));
    }


    /**
     * определяет типы процедур для конкретного заказчика и возвращает типы процедур
     *
     * @task https://zakupki.atlassian.net/browse/ZAK-491
     *
     */
    public function _getCustomerProcedures() {
        $kind = Auth::user()->organization->kind->kind;

        if($kind == 'defense') { //Замовник, що здійснює закупівлі для потреб оборони (1)

            return ProcedureTypes::orderBy('active', 'desc')->get();
        }

        if($kind == 'general') { //Замовник (загальний) (2)

            return ProcedureTypes::where('procurement_method_type', 'not like', '%aboveThresholdUA.defense%')->orderBy('active', 'desc')->get();
        }

        if($kind == 'other') { //Державні та комунальні підприємства, які не є замовниками в розумінні Закону (3)

            return ProcedureTypes::whereIn('procurement_method_type', ['reporting', 'belowThreshold'])->orderBy('active', 'desc')->get();
        }

        if($kind == 'special') { //Замовник, що здійснює діяльність в окремих сферах господарювання (4)

            return ProcedureTypes::where('procurement_method_type', 'not like', '%aboveThresholdUA.defense%')->orderBy('active', 'desc')->get();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Classifiers $classifier
     * @param int $procedureTypeId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Classifiers $classifier, $procedureTypeId = 1)
    {
        $documentTypes  = DocumentType::whereNamespace('tender')->get();
        $regions        = TendersRegions::orderBy('id')->active()->lists('region_ua', 'id');
        $currencies     = Currencies::lists('currency_description', 'id');
        $units          = Units::lists('description', 'id');
        $codes          = Codes::paginate(20);
        $classifiers    = $classifier->getNewClassifiersList();
        $languages = Languages::active()->lists('language_name', 'language_code');
        $procurementType = ProcurementType::lists('procurement_name', 'id')->all();
        $procedureType = ProcedureTypes::find($procedureTypeId);
        $procedureTypes = $this->_getCustomerProcedures();

        if ($procedureType->threshold_type == 'above') {
            if (empty(Auth::user()->organization->legal_name) || empty(Auth::user()->organization->legal_name_en)) {
                Session::flash('flash_modal', 'Вкажіть повну юридичну назву вашої організації.');
                return redirect()->route('organization.edit');
            }
        }

        if ($procedureType->procurement_method == 'limited') {
            $causes = \App\Model\NegotiationCause::lists('description', 'cause')->all();
        } else {
            $causes = [];
        }

        $organization = Auth::user()->organization;
        $userContacts = $organization->contacts;
        $contacts = array();
        foreach($userContacts as $contact){
            $contacts[$contact['id']] = $contact['contact_name'];
            if ($contact['primary']==1){
                $mainContact = $contact['id'];
            }
        }
        $template = $procedureType->procurement_method;
        if ($template == 'selective')
            $template = 'open';

        $i = 0;
        foreach($procedureTypes as $procedure){
            if($procedure['id'] == 8 && $organization->kind_id != 1) {
                $procedure['active'] = 0;
            }
            $procedureTypes[$i] = $procedure;
            $i++;
        }
        $regions[0] = 'Відповідно до документації';
        return view('pages.tender.create', compact('documentTypes', 'regions', 'currencies', 'units', 'codes',
            'organization', 'classifiers', 'procedureTypes','contacts','mainContact',
            'procurementType', 'template', 'procedureType', 'languages', 'causes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTenderRequest|Request $request
     * @return Response
     *
     */
    public function store(Request $request)
    {
        $tenderData = $request->all();
        if (isset($tenderData['tender_id']) && $tenderID = (int) $tenderData['tender_id'])
            return $this->update($request, $tenderID);

        if (isset($tenderData['enquiry_end_date'])) {
            $tenderData['enquiry_start_date'] = date('Y-m-d H:i:s');
            $tenderData['tender_start_date'] = Carbon::parse($tenderData['enquiry_end_date'])->addMinute()->format('Y-m-d H:i');
        }
        if($request->ajax()) {
            if ($tenderData['type_id'] == 4)
                $validator = ValidationService::validate(ValidationService::TENDER, $tenderData, ProcedureTypes::find($tenderData['type_id'])->procurement_method_type);
            else
                $validator = $this->_validate($tenderData);
            if($validator->fails()) {
                return response()->json([$validator->getMessageBag()->all()], 400);
            } else {
                return response()->json([], 200);
            }
        }
        if($tenderData['type_id'] != 4) {
            $tenderData['documentsCount'] = $this->_existDocuments($tenderData);
        }

        $validator = $this->_validate($tenderData);

        if ($validator->fails()) {
            return redirect('tender/create/'.$request->get('type_id'))
                ->withErrors($validator)
                ->withInput();
        }
        $tenderData['mode'] = $request->user()->organization->mode;
        $tenderData['source'] = 1;
        $tenderData['priority'] = 1;
        $tenderData['multilot'] = 1;
        if ($tenderData['type_id'] == 4) {
            $tenderData['multilot'] = 0;
        }

        $tender = new Tender($tenderData);
        $request->user()->organization->tenders()->save($tender);
        $this->_saveFeatures($tender, $tenderData, $tender->id);
//        if (isset($tenderData['contact_person'])) {
//            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($tenderData['contact_person'])]);
//            $tenderContacts->save();
//        }
        if (isset($tenderData['add_contact_person'])) {
            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($tenderData['add_contact_person'])]);
            $tenderContacts->save();
        }

        foreach ($tenderData['lots'] as $lotData) {
            $lot = new Lot($lotData);
            $tender->lots()->save($lot);
            if (isset($lotData['files'])) {
                $this->_uploadDocs($lot, $lotData['files'], "/tender/{$tender->id}/", $lotData['docTypes']);
            }
            foreach ($lotData['items'] as $itemData) {
                if (!isset($itemData['delivery_date_start']) || empty($itemData['delivery_date_start']))
                    $itemData['delivery_date_start'] = null;
                if($itemData['region_id'] == 0){
                    $itemData['delivery_address'] = 'Відповідно до документації';
                }
                $item = new Item($itemData);
                $lot->items()->save($item);
                $this->_saveFeatures($item, $itemData, $tender->id);
                $this->_uploadDocs($item, $itemData['files'], "/tender/{$tender->id}/", $itemData['docTypes']);
                $codes = [];
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
                $item->codes()->sync($codes);
            }
            $this->_saveFeatures($lot, $lotData, $tender->id);
        }
        if (isset($tenderData['additional_contact'])){
            foreach($tenderData['additional_contact'] as $contact){
                if(!empty($contact['id'])) {
                    $tc = new TenderContacts;
                    $tc->contact_id = $contact['id'];
                    $tc->tender_id = $tender->id;
                    $tc->save();
                }
            }
        }
        $this->_uploadDocs($tender, $request->file('tender')['files'], "/tender/{$tender->id}/", $tenderData['tender']['docTypes']);
        $this->_uploadDocs($tender, $request->file('newfiles'), "/tender/{$tender->id}/", [], true);
        if ($tender->procedureType->procurement_method_type == 'reporting') {
            Session::flash('flash_message', 'Зверніть увагу, що процедура буде опублікована на веб-порталі Уповноваженого органу лише після внесення інформації по переможцю і договору, накладення ЕЦП та завершення процедури');
        }
//        Session::flash('modal_flash_upload_docs', 'modal_flash_upload_docs');
        return redirect()->route('tender.show', $tender->id);
    }

    /**
     * Store a newly created resource in storage in status draft.
     *
     * @param CreateTenderRequest|Request $request
     * @return Response
     *
     */
    public function storeDraft(Request $request)
    {
        $tenderData = $request->all();

        if (isset($tenderData['enquiry_end_date'])) {
            $tenderData['enquiry_start_date'] = date('Y-m-d H:i:s');
            $tenderData['tender_start_date'] = Carbon::parse($tenderData['enquiry_end_date'])->addMinute()->format('Y-m-d H:i');
        }

        $validator = $this->_validate($tenderData);
        if($validator->fails()) {
            return response()->json([$validator->getMessageBag()->all()], 400);
        }

        $tenderData['mode'] = $request->user()->organization->mode;
        $tenderData['source'] = 1;
        $tenderData['multilot'] = 1;
        if ($tenderData['type_id'] == 4) {
            $tenderData['multilot'] = 0;
        }

        $tender = new Tender($tenderData);
        $request->user()->organization->tenders()->save($tender);
        $this->_saveFeatures($tender, $tenderData, $tender->id);
        if (isset($tenderData['contact_person'])) {
            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($tenderData['contact_person'])]);
            $tenderContacts->save();
        }
        if (isset($tenderData['add_contact_person'])) {
            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($tenderData['add_contact_person'])]);
            $tenderContacts->save();
        }

        foreach ($tenderData['lots'] as $lotData) {
            $lot = new Lot($lotData);
            $tender->lots()->save($lot);
            foreach ($lotData['items'] as $itemData) {
                if (!isset($itemData['delivery_date_start']) || empty($itemData['delivery_date_start']))
                    $itemData['delivery_date_start'] = null;
                if($itemData['region_id'] == 0){
                    $itemData['delivery_address'] = 'Відповідно до документації';
                }
                $item = new Item($itemData);
                $lot->items()->save($item);
                $this->_saveFeatures($item, $itemData, $tender->id);
                $codes = [];
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
                $item->codes()->sync($codes);
            }
            $this->_saveFeatures($lot, $lotData, $tender->id);
        }

        $this->_uploadDocs($tender, $request->file('newfiles'), "/tender/{$tender->id}/", [], true);

        return response()->json(['status'=> 'created', 'tender_id' => $tender->id], 200);
    }

    public function sync($id)
    {
        $tender = Tender::find($id);
        $this->dispatch((new SyncTender($tender->cbd_id))->onQueue('tenders_high'));

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function show($id)
    {
        $tender = Tender::findOrFail($id);

        $template = $tender->procedureType->procurement_method;
        if ($template == 'selective') {
            $template = 'open';
        }

        $procedureType = $tender->procedureType;
        $documentTypes = DocumentType::where('namespace', 'tender')->get();

        $isAboveThresholdEU = false;
        if ($tender->procedureType->procurement_method_type == 'aboveThresholdEU') {
            $isAboveThresholdEU = true;
            $language = Languages::where('language_code', $tender->contact_available_lang)->first();
        }
        $language = Languages::active()->lists('language_name', 'language_code')->toArray();

        $tender->documents = $tender->documents->sortBy(function ($item) {
            return $item->orig_id . Carbon::parse($item->created_at)->timestamp . $item->id;
        })->reverse();
        $metaTags = [];
        $metaTags['title'] =  "".$tender->organization->name." | Публічні закупівлі ProZorro | Електронний майданчик Zakupki UA";
        $metaTags['description'] =  $tender->title;
        $metaTags['keywords'] =  $tender->title." , ".$tender->organization->name;
        $additionalContacts = \App\Model\TenderContact::where('tender_id',$tender->id)->get();

        return view('pages.tender.' . $template . '.detail', compact('tender', 'template','additionalContacts','metaTags', 'procedureType', 'isAboveThresholdEU', 'language', 'documentTypes'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param $tua
     */
    public function showByTenderID(Request $request)
    {
        $tender = Tender::where('tenderID', $request->get('TenderID'))->first();
        if ($tender) return $this->show($tender->id);
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Classifiers $classifier
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Classifiers $classifier, $id)
    {
        if (Auth::user()->super_user) {
            $tender = Tender::find($id);
        } else {
            $tender = Auth::user()
                ->organization
                ->tenders()->findOrFail($id);
        }

        $documentTypes = DocumentType::where('namespace', 'tender')->get();
        $regions = TendersRegions::orderBy('id')->active()->lists('region_ua', 'id');
        $currencies = Currencies::lists('currency_description', 'id');
        $units = Units::lists('description', 'id');
        $classifiers = $classifier->getClassifiersList();
        $procedureTypes = $this->_getCustomerProcedures();
        $procurementType = ProcurementType::lists('procurement_name', 'id')->all();
        $languages = Languages::active()->lists('language_name', 'language_code');
        $additionalContacts = \App\Model\TenderContact::where('tender_id',$tender->id)->get();

        if ($tender->procedureType->procurement_method == 'limited') {
            $causes = \App\Model\NegotiationCause::lists('description', 'cause')->all();
        } else {
            $causes = [];
        }

        $organization = Auth::user()->organization;
        $userContacts = $organization->contacts;
        $contacts = array();
        $primoryContactId = 0;
        foreach($userContacts as $contact){
            $contacts[$contact['id']] = $contact['contact_name'];
            if ($contact['primary']==1)
                $primoryContactId = $contact['id'];

            if ($contact['contact_name'] == $tender->contact_name)
                $mainContact = $contact['id'];
        }
        if (empty($mainContact))
            $mainContact = $primoryContactId;
        $procedureType = $tender->procedureType;
        $template = $tender->procedureType->procurement_method;
        $regions[0] = 'Відповідно до документації';
        if ($template == 'selective')
            $template = 'open';

        return view('pages.tender.edit', compact('tender','additionalContacts', 'documentTypes','organization', 'currencies', 'regions', 'units', 'contacts','mainContact',
            'classifiers', 'procedureTypes', 'procurementType', 'template', 'procedureType', 'languages', 'causes'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param CreateTenderRequest|Request $request
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function update(Request $request, $id)
    {

        $request_all = $request->all();
        $tender = Auth::user()
            ->organization
            ->tenders()->with('lots')->findOrFail($id);

        if ($tender->procedureType && $tender->procedureType->procurement_method_type == 'belowThreshold' && isset($request_all['enquiry_end_date'])) {
            if (!$tender->isPublished()) {
                $request_all['enquiry_start_date'] = date('Y-m-d H:i:s');
            } else {
                $request_all['enquiry_start_date'] = $tender->enquiry_start_date;
            }
            $request_all['tender_start_date'] = Carbon::parse($request_all['enquiry_end_date'])->addMinute()->format('Y-m-d H:i');
        } elseif (is_object($tender->procedureType) && $tender->procedureType->procurement_method == 'open') {
            if (!$tender->isPublished()) {
                $request_all['tender_start_date'] = date('Y-m-d H:i:s');
            } else {
                $request_all['tender_start_date'] = $tender->tender_start_date;
            }
        }

        if($request->ajax()) {
            $validator = $this->_validate($request_all, $tender);
            if($validator->fails()) {
                return response()->json([$validator->getMessageBag()->all()], 400);
            } else {
                return response()->json([], 200);
            }
        }

        $validator = $this->_validate($request_all, $tender);
        if ($validator->fails()) {
            return redirect()->route('tender.edit', [$id])
                ->withErrors($validator)
                ->withInput();
        }


        if (!isset($request_all['tax_included'])){
            $request_all['tax_included'] = 0;
        }
        if ($tender->procedureType->threshold_type == 'above') {
            $request_all['signed'] = 0;
            $notification_service = new NotificationService();
            $tags = new Tags();
            $notification_service->create($tags, NotificationTemplate::USER_ECP_CREATED, $tender->organization->user->id, self::DEFAULT_LANGUAGE);
        }
        if (in_array($tender->procedureType->threshold_type,['below','below.limited','above.limited'])) {
        	$request_all['signed'] = 0;
        }
        $tender->update($request_all);
        TenderContact::where('tender_id',$tender->id)->delete();
        if (isset($request_all['additional_contact'])){
            foreach($request_all['additional_contact'] as $contact){
                if(!empty($contact['id'])) {
                    $tc = new TenderContact;
                    $tc->contact_id = $contact['id'];
                    $tc->tender_id = $tender->id;
                    $tc->save();
                }
            }
        }
        // Зачем это ? Если основной контакт хранится в Тендере.
//        if (isset($request_all['contact_person'])) {
//            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($request_all['contact_person'])]);
//            $tenderContacts->save();
//        }
        if (isset($request_all['add_contact_person'])) {
            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($request_all['add_contact_person'])]);
            $tenderContacts->save();
        }

        if (isset($request_all['features'])){
            $this->_saveFeatures($tender, $request_all, $tender->id);
        }

        $lotIds = [];
        foreach ($request_all['lots'] as $lotData) {
            if (isset($lotData['id']) && !empty($lotData['id'])) {
                if(isset($lotData['guarantee_type']) && $lotData['guarantee_type'] === 'ns') {
                    $lotData['guarantee_amount'] = NULL;
                    $lotData['guarantee_currency_id'] = NULL;
                }
                $lot = $tender->lots->find($lotData['id']);
                $lot->update($lotData);
            } else {
                $lot = new Lot($lotData);
                $tender->lots()->save($lot);
            }

            $this->_saveFeatures($lot, $lotData, $tender->id);

            $lotIds[] = $lot->id;
            if (isset($lotData['files'])) {
                $this->_uploadDocs($lot, $lotData['files'], "/tender/$id/", $lotData['docTypes']);
            }


            $itemIds = [];
            foreach ($lotData['items'] as $itemData) {
                if (!isset($itemData['delivery_date_start']) || empty($itemData['delivery_date_start']))
                    $itemData['delivery_date_start'] = null;
                if($itemData['region_id'] == 0){
                    $itemData['delivery_address'] = 'Відповідно до документації';
                }
                if (isset($itemData['id']) && !empty($itemData['id'])) {
                    $item = $lot->items->find($itemData['id']);
                    $item->update($itemData);
                }else {
                    $item = new Item($itemData);
                    $lot->items()->save($item);
                }
                $this->_saveFeatures($item, $itemData, $tender->id);
                $codes = [];
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
                $item->codes()->sync($codes);
                if (isset($itemData['files'])) {
                    $this->_uploadDocs($item, $itemData['files'], "/tender/$id/", $itemData['docTypes']);
                }
                $itemIds[] = $item->id;
            }
            $lot->items()->whereNotIn('id', $itemIds)->delete();
        }

        if ($tender->status == 'draft') {
            $tender->lots()->whereNotIn('id', $lotIds)->delete();
        }

        $this->_uploadDocs($tender, $request->file('tender')['files'], "/tender/$id/", $request_all['tender']['docTypes']);
        $this->_uploadDocs($tender, $request->file('newfiles'), "/tender/$id/", [], true);
        if ($tender->status != 'draft') {
            Event::fire(new TenderSaveEvent($tender));
        }

        if ($tender->status != 'draft' && ($tender->procedureType->procurement_method_type == 'aboveThresholdEU' || $tender->procedureType->procurement_method_type == 'competitiveDialogueEU' || $tender->procedureType->procurement_method_type == 'competitiveDialogueUA' || $tender->procedureType->procurement_method == 'selective')) {
            foreach ($tender->allBids as $bid) {
                $bid->status = 'invalid';
                $bid->save();
            }
        }

        Session::flash('flash_message', 'Дані успішно оновлені');
        return redirect()->route('tender.show', [$tender->id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CreateTenderRequest|Request $request
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function updateDraft(Request $request)
    {
        $request_all = $request->all();

        $tender = Auth::user()
            ->organization
            ->tenders()->with('lots')->findOrFail($request_all['tender_id']);

        if ($tender->status != 'draft') {
            return response()->json([], 200);
        }

        if ($tender->procedureType && $tender->procedureType->procurement_method_type == 'belowThreshold' && isset($request_all['enquiry_end_date'])) {
            if (!$tender->isPublished()) {
                $request_all['enquiry_start_date'] = date('Y-m-d H:i:s');
            } else {
                $request_all['enquiry_start_date'] = $tender->enquiry_start_date;
            }
            $request_all['tender_start_date'] = Carbon::parse($request_all['enquiry_end_date'])->addMinute()->format('Y-m-d H:i');
        } elseif (is_object($tender->procedureType) && $tender->procedureType->procurement_method == 'open') {
            if (!$tender->isPublished()) {
                $request_all['tender_start_date'] = date('Y-m-d H:i:s');
            } else {
                $request_all['tender_start_date'] = $tender->tender_start_date;
            }
        }

        $validator = $this->_validate($request_all, $tender);
        if ($validator->fails()) {
            return response()->json([$validator->getMessageBag()->all()], 400);
        }

        if (!isset($request_all['tax_included'])){
            $request_all['tax_included'] = 0;
        }
        if ($tender->procedureType->threshold_type == 'above') {
            $request_all['signed'] = 0;
        }
        $tender->update($request_all);

        TenderContacts::where('tender_id', $tender->id)->delete();
        if (isset($request_all['contact_person'])) {
            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($request_all['contact_person'])]);
            $tenderContacts->save();
        }
        if (isset($request_all['add_contact_person'])) {
            $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => intval($request_all['add_contact_person'])]);
            $tenderContacts->save();
        }

        if (isset($request_all['features'])){
            $this->_saveFeatures($tender, $request_all, $tender->id);
        }

        $lotIds = [];
        foreach ($request_all['lots'] as $lotData) {
            if (isset($lotData['id']) && !empty($lotData['id'])) {
                if(isset($lotData['guarantee_type']) && $lotData['guarantee_type'] === 'ns') {
                    $lotData['guarantee_amount'] = NULL;
                    $lotData['guarantee_currency_id'] = NULL;
                }
                $lot = $tender->lots->find($lotData['id']);
                $lot->update($lotData);
            } else {
                $lot = new Lot($lotData);
                $tender->lots()->save($lot);
            }

            $this->_saveFeatures($lot, $lotData, $tender->id);

            $lotIds[] = $lot->id;

            $itemIds = [];
            foreach ($lotData['items'] as $itemData) {
                if (isset($itemData['id']) && !empty($itemData['id'])) {
                    $item = $lot->items->find($itemData['id']);
                    $item->update($itemData);
                }else {
                    $item = new Item($itemData);
                    $lot->items()->save($item);
                }
                $this->_saveFeatures($item, $itemData, $tender->id);
                $codes = [];
                foreach ($itemData['codes'] as $code) {
                    $codes[] = $code['id'];
                }
                $item->codes()->sync($codes);
                $itemIds[] = $item->id;
            }
            $lot->items()->whereNotIn('id', $itemIds)->delete();
        }

        if ($tender->status == 'draft') {
            $tender->lots()->whereNotIn('id', $lotIds)->delete();
        }

        $this->_uploadDocs($tender, $request->file('newfiles'), "/tender/" . $tender->id . "/", [], true);

        return response()->json(['status'=> 'updated', 'tender_id' => $tender->id], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $tender = Auth::user()
            ->organization
            ->tenders()->findOrFail($id);
        if ($tender->status == 'draft') {
            $tender->status = 'deleted';
            $tender->update();
            Session::flash('flash_message', 'Тендер видалено.');
        } else {
            Session::flash('flash_message', 'Не можливо видалити тендер.');
        }
        return redirect()->route('tender.index');
    }

    public function publish($id)
    {
        if (Auth::user()->super_user) {
            $tender = Tender::find($id);
        } else {
            $tender = Auth::user()
                ->organization
                ->tenders()->findOrFail($id);
        }

        if ($tender->mode == 1 && Auth::user()->organization->mode != 1) {
            Session::flash('flash_error', 'Для публікації закупівлі змініть режим роботи на Реальний.');
            return redirect()->back();
        }

        //Event::fire(new TenderPublishEvent($tender));

        if ($tender->enquiry_end_date != null) {
            $validator = $this->validationPublish($tender);
            if ($validator) {
                Session::flash('flash_error',$validator);
                return redirect()->back();
            }
        }
        if ($tender->type_id == 8) {
            $tender = Tender::findOrFail($id);
            $tenderStartDate = strtotime($tender->tender_start_date);
            $daysFromStartToEnd = getWorkdaysBetweenDates($tenderStartDate, getDateWithWorkdays(time(), 3));
            $minDayEnd = 3;
            if ($daysFromStartToEnd < 6)
                $minDayEnd = 6;

            $minTimeTxt = date('Y-m-d H:i:s', getDateWithWorkdays(time(), $minDayEnd));
            $tenderData['tender_end_date'] = $tender->tender_end_date;
            $rules['tender_end_date'] = 'required|date|after:' . $minTimeTxt;
            $messages['tender_end_date.after'] = 'Кінцевий строк подання тендерних пропозицій повинен бути не раніше, ніж ' . $minTimeTxt;
            $validator =  Validator::make($tenderData, $rules, $messages);
            if ($validator->fails() != false) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $tender->status = 'published';
        $tender->save();

        Event::fire(new TenderSaveEvent($tender));

        Session::flash('flash_message', 'Закупівля відправлена на публікацію');
        Session::flash('flash_modal', "                        <h2>Шановний Користувач.</h2>
                        <p>&nbsp;</p>
                        <p>Будь ласка, перевірте публікацію Вашого документу на веб-порталі Уповноваженого органу через ".$_ENV["SINHRONIZATION_TIME"]." хвилин.
                        <br>У випадку, якщо документ не був опублікований, повідомте про це нашу службу підтримки за телефоном +380684921428
                         <br>або відправивши лист на адресу support@zakupki.com.ua”.
                           </p>
                        <p>&nbsp;</p>
");
        return redirect()->route('tender.show', [$id]);
    }

    public function validationPublish($tenderObject)
    {
        return false;
        if (env('APP_ENV') != 'server') {

            return false;
        }
        $differnce = strtotime($tenderObject->enquiry_end_date) - strtotime(date('d.m.Y H:i'));

        if ($differnce < 172800 || strtotime($tenderObject->enquiry_end_date) < strtotime(date('d.m.Y H:i'))){
            $dateStart = strtotime(date('d.m.Y H:i'))+172800;
            $bestDate = date("d.m.Y H:i", $dateStart);
            $msg =  'Дата та час закінчення періоду уточнень повинні бути не раніше ніж '.$bestDate.' Відредагуйте, будь ласка, це поле та спробуйте знову опублікувати закупівлю';
            return $msg;
        }
        else{
            return false;
        }
    }

    public function classifier(Request $request, $type)
    {
        $term = $request->input('term');
        $codes = Codes::select(['codes.id', \DB::raw('CONCAT(code, " ", description) AS value')])
            ->where(function ($query) use ($term) {
                $query->where('code', 'LIKE', "$term%")
                    ->orWhere('description', 'LIKE', "%$term%");
                })
            ->join('classifiers', 'codes.type', '=', 'classifiers.id');
        if ($type != 0) {
            $codes = $codes->where('type', $type);
        }

        return $codes->get();
    }

    /*
     * performs simple validation if string looks like a signature
     */
    protected static function isValidSignature($sign)
    {
        if (empty($sign)) return false;

        $sign = base64_decode($sign);
        if (!$sign) return false;

        if (strlen($sign) < 15) return false;

        // check if starts with SEQUENCE tag
        $v = unpack('C2tag/nlen', $sign);
        if ($v['tag1'] !== 0x30) return false;
        if ($v['tag2'] !== 0x82) return false;
        if ($v['len'] !== strlen($sign) - 4) return false;

        $sign = substr($sign, 4);

        // check if OID follows
        $v = unpack('Ctype/Clen', $sign);
        if ($v['type'] !== 0x06) return false;
        if ($v['len'] !== 9) return false;

        $oid = substr($sign, 2, 9);

        // check if OID is 1.2.840.113549.1.7.2 (pkcs7-signedData)
        if ($oid !== "\x2A\x86\x48\x86\xF7\x0D\x01\x07\x02") return false;

        return true;
    }

    public function test()
    {
        Artisan::call('add', [
            'id' => Auth::user()->organization->id
        ]);

        return redirect()->route('tender.index');
    }

    public function getSign(Request $request, $id)
    {
        $tender = Tender::find($id);
        $sign = $tender->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();

        $api = new Api(false);
        $result = $api->get($tender->cbd_id);
        if ($sign) {
            $result['sign'] = file_get_contents($sign->url);
        } else {
            $result['sign'] = false;
        }

        return $result;
    }

    public function postSign(Request $request, $id)
    {
        $sign = $request->get('sign');
        if (self::isValidSignature($sign)) {

            if (Auth::user()->super_user) {
                $tender = Tender::find($id);
            } else {
                $tender = Auth::user()
                    ->organization
                    ->tenders()->findOrFail($id);
            }

            Storage::disk('documents')->put("/tender/$id/sign.p7s", $sign);
            $document = $tender->documents()->where('title', 'sign.p7s')->orderBy('created_at', 'DESC')->first();
            if (!$document) {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'path' => "/tender/$id/sign.p7s",
                    'tender_id' => $tender->id,
                    'status' => 'new'
                ];

                $newDocument = new Document($params);
                $tender->documents()->save($newDocument);
            } else {
                $params = [
                    'title' => 'sign.p7s',
                    'description' => '',
                    'format' => 'application/pkcs7-signature',
                    'orig_id' => $document->orig_id,
                    'path' => "/tender/$id/sign.p7s",
                    'tender_id' => $tender->id,
                    'status' => 'new'
                ];
                $document->status = 'old';
                $document->save();

                $existingDocument = new Document($params);
                $tender->documents()->save($existingDocument);
            }
            $tender->signed = 1;
            $tender->save();

            Event::fire(new TenderSaveEvent($tender));

            return ['result' => 'success', 'message' => ''];
        }

        return ['result' => 'failed', 'message' => 'Підпис не переданий'];
    }

    /**
     * qualify tender
     *
     * @param  int $id
     * @return Response
     */
    public function qualify(Request $request, $id)
    {
        $tender = Tender::findOrFail($id);
//        if ($tender->multilot) {
//            $lotID = $request->input('lot_id');
//            $bids = Lot::findOrFail($lotID)->bids;
//        } else {
        $bids = $tender->allBids;
//        }
        foreach ($bids as $bid) {
            $qualification = $bid->qualification;
            if ($qualification && $qualification->status != 'active' && $qualification->status != 'unsuccessful') {
                $rules['all_qualifications'] = 'required';
                $messages['all_qualifications.required'] = "Не всі пропозиції було розлянуто.";

                $validator = Validator::make([], $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->route('tender.bids', [$tender->id])
                        ->withErrors($validator)
                        ->withInput();
                }
            }
        }
        $tender->status = 'active.pre-qualification.stand-still';
        $tender->save();

        Event::fire(new TenderUpdateEvent($tender));
        Session::flash('flash_message', 'Прекваліфікація завершена!');

        return redirect()->back();
    }

    public function getTenders()
    {
        ini_set('memory_limit', '2048M');
        if (Auth::user()->super_user){

            $date_start = (new Carbon('first day of last month'));
            $date_end = (new Carbon('first day of this month'));

            $tenders = Tender::where('access_token','!=','')
                ->where(function($query) {
                    $query->where('status','complete')
                        ->orWhere('status','unsuccessful')
                        ->orWhere('status','cancelled');
                })
                ->where('type_id','!=',5)
                ->where('type_id','!=',6)
                ->where('type_id','!=',4)
                ->where('mode',1)
                ->whereBetween('date_modified',[$date_start->toDateString(), $date_end->toDateString()])
                ->whereHas('organization',function($q){
                    $q->where('kind_id', '!=', '3');
                })->get();

            $results = [];

            foreach ($tenders as $tender) {
                $results[] = $tender->toArray();
            }

            Excel::create('tenders_'.$date_start->format('m.Y'), function($excel) use ($results) {
                $excel->sheet('Скарги', function($sheet) use ($results) {
                    $sheet->fromArray($results);
                });
            })->download('xlsx');

        } else {
            return redirect()->back();
        }

    }

    public function completeFirstStage($id){
        if (Auth::user()->super_user) {
            $tender = Tender::find($id);
        } else {
            $tender = Auth::user()
                ->organization
                ->tenders()->findOrFail($id);
        }

        if (isset($tender)) {
            $this->dispatch((new ChangeStatus($tender, 'active.stage2.waiting')));
        }

        return redirect()->back();
    }

    public function publishSecondStage($id)
    {
        if (Auth::user()->super_user) {
            $tender = Tender::find($id);
        } else {
            $tender = Auth::user()
                ->organization
                ->tenders()->findOrFail($id);
        }

        strtotime($tender->tender_end_date);
        $minDateEnd = time() + (7 * 86400 +3600) / env('API_ACCELERATOR');
        if (strtotime($tender->tender_end_date) < $minDateEnd) {
            Session::flash('flash_error', 'Період подачі пропозицій повинен бути не раніше, ніж ' . date('d.m.Y. H:i', $minDateEnd));
            return redirect()->back();
        }

        if (isset($tender)) {
            $this->dispatch((new GetTenderCredentials($tender->cbd_id)));
        }

        return redirect()->back();
    }

    public function stat($date = null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
        $endDate = Carbon::parse($date)->addDay(1);

        $count = Tender::where('created_at', '>', $date)->where('created_at', '<', $endDate)->get()->count();
        $groupCount = DB::table('tenders')
            ->select('procedure_name', DB::raw('count(*) as total'))
            ->join('procedure_types', 'procedure_types.id', '=', 'tenders.type_id')
            ->where('created_at', '>', $date)
            ->where('created_at', '<', $endDate)
            ->groupBy('type_id')
            ->get();
        return [
            'date' => $date,
            'all' => $count,
            'group' => $groupCount
        ];
    }

    public function participants($id)
    {
        $tender = Tender::find($id);
        $firstStage = $tender->stages->firstStage;

        return view('pages.bids.participants', ['tender' => $tender, 'firstStage' => $firstStage]);
    }

    /**
     * return 1 if exist at least 1 document and 0 if documents doesn`t exist
     *
     * @param $tenderData
     * @return bool|int
     */
    protected function _existDocuments($tenderData)
    {
        if (isset($tenderData['tender']['files']) && count($tenderData['tender']['files']) > 1)
            return 1;
        foreach ($tenderData['lots'] as $lotData) {
            if (isset($lotData['files']) && count($lotData['files']) > 1)
                return 1;

            foreach ($lotData['items'] as $itemData) {
                if (isset($itemData['files']) && count($itemData['files']) > 1)
                    return 1;
            }
        }
        return 0;
    }

}
