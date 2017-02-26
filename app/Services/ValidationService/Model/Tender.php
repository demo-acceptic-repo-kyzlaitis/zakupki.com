<?php

namespace App\Services\ValidationService\Model;


use App\Model\Codes;
use App\Services\ValidationService\Rules\Tenders\iTenderRules;
use Carbon\Carbon;

/**
 * Model for validation
 * Class Tender
 * @package App\Services\ValidationService\Model
 */
class Tender implements iModelValidation
{
    /**
     * input data
     * @var
     */
    protected $data;

    /**
     * instance of tender type rule
     * @var iTenderRules
     */
    protected $_tenderType;

    /**
     * Tender constructor.
     * @param $data
     * @param iTenderRules $type
     * @throws \Exception
     */
    public function __construct($data, iTenderRules $type) {
        $this->data = $this->preProcessing($data);
        $this->_tenderType = $type;
        $this->_tenderType->setTenderData($this->data);
        if (!$this->_tenderType)
            throw new \Exception('Bad tender type');
    }

    /**
     * return processed data
     * @param $data
     * @return array
     */
    protected function preProcessing($data) {
        /**
         * calculate total amount for multilot tender
         */
        if (empty($data['amount'])) {
            $data['amount'] = 0;
            if (isset($data['lots']))
                foreach ($data['lots'] as $lot)
                    if (isset($lot['amount']))
                        $data['amount'] += (int) $lot['amount'];
        }

        /**
         * add enquiry and tender start date if exists enquiry end date
         */
        if (isset($data['enquiry_end_date'])) {
            $data['enquiry_start_date'] = date('Y-m-d H:i:s');
            $data['tender_start_date'] = Carbon::parse($data['enquiry_end_date'])->addMinute()->format('Y-m-d H:i');
        }

        /**
         * calculate documents count
         */
        $data['documentsCount'] = $this->_existDocuments($data);

        /**
         * processing the similarity of codes
         */
        $data = (empty($data['classifier_code_validation'])) ? $this->_validateCodes($data) : $this->_validateCodes($data, 4);

        return $data;
    }

    /**
     * get data for validaor
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * get all rules (tender, lot and item) for validator
     * @return array
     */
    public function getRules() {
        $rules = $this->_tenderType->getTenderRules();
        if (empty($this->data['lots']))
            return $rules;

        foreach ($this->data['lots'] as $lotIndex => $lotData) {
            $lot = $this->changeArrayKeys($this->_tenderType->getLotRules(), 'lots.'.$lotIndex.'.');
            $rules = array_merge($rules, $lot);
            foreach ($lotData['items'] as $itemIndex => $itemData) {
                $itemCodes = (isset($itemData['codes'])) ? $itemData['codes'] : [];
                $item = $this->changeArrayKeys($this->_tenderType->getItemRules($itemCodes), 'lots.'.$lotIndex.'.items.'.$itemIndex.'.');
                $rules = array_merge($rules, $item);
            }
        }

        return $rules;
    }

    /**
     * get all messages (tender, lot and item) for validator
     * @return array
     */
    public function getMessages() {
        $messages = $this->_tenderType->getTenderMessages();
        if (empty($this->data['lots']))
            return $messages;

        foreach ($this->data['lots'] as $lotIndex => $lotData) {
            $lot = $this->changeArrayKeys($this->_tenderType->getLotMessages($lotIndex+1), 'lots.'.$lotIndex.'.');
            $messages = array_merge($messages, $lot);
            foreach ($lotData['items'] as $itemIndex => $itemData) {
                $itemCodes = (isset($itemData['codes'])) ? $itemData['codes'] : [];
                $item = $this->changeArrayKeys($this->_tenderType->getItemMessages($lotIndex+1, $itemIndex+1, $itemCodes), 'lots.'.$lotIndex.'.items.'.$itemIndex.'.');
                $messages = array_merge($messages, $item);
            }
        }

        return $messages;
    }

    /**
     * return the same array, but keys will be changed (string + old key)
     * @param $array
     * @param $str
     * @return array
     */
    protected function changeArrayKeys($array, $str) {
        $newArray = [];
        foreach ($array as $key => $val)
            $newArray[$str . $key] = $val;

        return $newArray;
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

        if (empty($tenderData['lots']))
            return 0;

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

    /**
     * get all items codes
     * @param $tenderData
     * @return array
     */
    protected function _getItemsCodes($tenderData)
    {
        if (empty($tenderData['lots']))
            return [];

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

    /**
     * validate classifier codes
     * @param $tenderData
     * @param int $charNum
     * @return mixed
     */
    protected function _validateCodes($tenderData, $charNum = 3)
    {
        $codesIds = $this->_getItemsCodes($tenderData);
        $codes = Codes::whereIn('id', $codesIds)->get();
        if ($codes->count() > 0) {
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
        }

        return $tenderData;
    }
}