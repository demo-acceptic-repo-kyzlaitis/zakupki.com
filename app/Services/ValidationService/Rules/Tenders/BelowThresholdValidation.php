<?php

namespace App\Services\ValidationService\Rules\Tenders;


class BelowThresholdValidation extends BaseValidation
{

    /**
     * @var self
     */
    private static $instance;

    /**
     * return self
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * array of tender rules
     * @return array
     */
    public function getTenderRules() {
        $rules = parent::getTenderRules();

        //TODO add documents count
//        $rules['documentsCount'] = 'in:1';
        if ($this->tenderData['amount'] < 50000) {
            $this->data['tenderDuration'] = $this->data['enquiryDuration'] = 2;
        } else {
            $this->data['enquiryDuration'] = 4;
            $this->data['tenderDuration'] = 3;
        }

        if (!isset($this->tenderData['enquiry_end_date']))
            $this->tenderData['enquiry_end_date'] = null;

        $rules['enquiry_end_date'] = 'required|date';
        $rules['tender_end_date'] = 'required|date';

        $this->data['minEnquiryEndDate'] = getDateWithWorkdays(time(), $this->data['enquiryDuration']);
        $this->data['minTenderEndDate'] = getDateWithWorkdays(strtotime($this->tenderData['enquiry_end_date']), $this->data['tenderDuration']);

        if ($this->data['minEnquiryEndDate'] < strtotime($this->tenderData['enquiry_end_date'])) {
            $rules['tender_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', $this->data['minTenderEndDate']);
        } else {
            $rules['enquiry_end_date'] = 'required|date|after:' . date('Y-m-d H:i:s', $this->data['minEnquiryEndDate']);
        }

        return $rules;
    }

    /**
     * array of tender messages
     * @return array
     */
    public function getTenderMessages() {
        $messages = parent::getTenderMessages();

        $messages['documentsCount.in'] = 'Для створення закупівлі додайте документи';

        $messages['enquiry_end_date.required'] = 'Поле "Закінчення періоду уточнень" повинно бути заповнене';
        $messages['enquiry_end_date.date'] = 'Поле "Закінчення періоду уточнень" повинно бути датою';
        $messages['enquiry_end_date.after'] = 'Поле "Закінчення періоду уточнень" повинно бути не раніше ніж ' . date('Y-m-d H:i:s', $this->data['minEnquiryEndDate']);
        $messages['tender_end_date.required'] = 'Поле "Кінцевий строк подання тендерних пропозицій" повинно бути заповнене';
        $messages['tender_end_date.date'] = 'Поле "Кінцевий строк подання тендерних пропозицій" повинно бути датою';
        $messages['tender_end_date.after'] = 'Поле "Кінцевий строк подання тендерних пропозицій" повинно бути не раніше ніж ' . date('Y-m-d H:i:s', $this->data['minTenderEndDate']);

        return $messages;
    }

    /**
     * array of lot rules
     * @return array
     */
    public function getLotRules() {
        $rules = parent::getLotRules();

        $minStep = (int)$this->tenderData['amount'] * 0.5 / 100;
        $maxStep = (int)$this->tenderData['amount'] * 3 / 100;
        $rules['minimal_step'] = 'required|numeric|max:' . $maxStep . '|min:' . $minStep;

        return $rules;
    }

    /**
     * array of lot messages
     * @param null $lotIndex
     * @return array
     */
    public function getLotMessages($lotIndex = null) {
        $messages = parent::getLotMessages($lotIndex);

        $messages['minimal_step.max'] = "Максимальний крок аукціону в лоті $lotIndex не повинен перевищувати 3%.";
        $messages['minimal_step.min'] = "Мінімальний крок аукціону в лоті $lotIndex повинен бути не менше 0.5% .";

        return $messages;
    }

    /**
     * constructor closed
     */
    private function __construct(){}

    /**
     * clone closed
     */
    private function __clone(){}

    /**
     * serialize closed
     */
    private function __sleep(){}

    /**
     * deserialize closed
     */
    private function __wakeup(){}
}
