<?php

namespace App\Services\ValidationService\Rules\Tenders;


class ReportingValidation extends BaseValidation
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
        $rules['amount'] = 'numeric|min:0.01';
        return $rules;
    }

    /**
     * array of tender messages
     * @return array
     */
    public function getTenderMessages() {
        $messages = parent::getTenderMessages();
        $messages['amount.min'] = 'Очікувана вартість закупівлі не може бути меншою, ніж 1 коп.';
        return $messages;
    }

    /**
     * array of lot rules
     * @return array
     */
    public function getLotRules() {
        $rules = parent::getLotRules();

        unset($rules['title']);
        unset($rules['description']);
        unset($rules['amount']);
        unset($rules['minimal_step']);

        return $rules;
    }

    /**
     * array of item messages
     * @param null $lotIndex
     * @param null $itemIndex
     * @param array $itemCodes
     * @return array
     */
    public function getItemMessages($lotIndex = null, $itemIndex = null, $itemCodes = []) {
        $messages = parent::getItemMessages($lotIndex, $itemIndex, $itemCodes);
        $lotIndexStr = ($lotIndex >= 0 && $itemIndex >= 0) ? "Лот $lotIndex, товар $itemIndex. " : '';
        $messages['description.required'] = $lotIndexStr . "Поле \"Назва предмета закупівлі\" повинно бути заповнене";
        $messages['delivery_date_start.required'] = $lotIndexStr . "Поле \"Строки доставки з\" повинно бути заповнене";
        $messages['delivery_date_start.date'] = $lotIndexStr . "Поле \"Строки доставки з\" повинно бути датою";
        $messages['delivery_date_end.required'] = $lotIndexStr . "Поле \"Строки доставки по\" повинно бути заповнене";
        $messages['delivery_date_end.date'] = $lotIndexStr . "Поле \"Строки доставки по\" повинно бути датою";
        $messages['delivery_date_end.after'] = $lotIndexStr . "Поле \"Строки доставки по\" повинно бути пізніше ніж \"Період доставки з\"";

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
