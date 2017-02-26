<?php

namespace App\Services\ValidationService\Rules\Tenders;


class BaseValidation implements iTenderRules
{

    /**
     * @var
     */
    public $data;

    /**
     * @var
     */
    public $tenderData;

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
     * set tender data
     * @param array $tenderData
     */
    public function setTenderData($tenderData = [])
    {
        $this->tenderData = $tenderData;
    }

    /**
     * array of tender rules
     * @return array
     */
    public function getTenderRules() {
        return [
            'title' => 'required|max:255',
            'description' => 'required',
            'amount' => 'numeric|min:3000',
            'currency_id' => 'required|integer',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|regex:"\+380\d{3,12}"',
            'contact_email' => 'required|email',
            'contact_url' => 'url',
            'cpv_codes_count' => 'in:1',
            'dkpp_codes_count' => 'in:1',
        ];
    }

    /**
     * array of tender messages
     * @return array
     */
    public function getTenderMessages() {
        return [
            'title.required' => "Поле \"Конкретна назва закупівлі\" необхідно заповнити",
            'title.max' => "Максимальна довжина поля \"Конкретна назва закупівлі\" не повинна перевижувати 255 символів",
            'description.required' => "Поле \"Загальні відомості про закупівлю\" необхідно заповнити",
            'amount.min' => "Очікувана вартість закупівлі не може бути меншою, ніж 3000 грн.",
            'amount.numeric' => 'Поле "Бюджет лоту" повинно бути числом',
            'currency_id.required' => 'Поле "Валюта" необхідно заповнити',
            'currency_id.integer' => 'Виберіть валюту з випадаючого списку',
            'contact_name.required' => 'Поле "Контактна особа" необхідно заповнити',
            'contact_name.string' => 'Поле "Контактна особа" повинно бути рядком',
            'contact_phone.required' => 'Поле "Телефон" необхідно заповнити',
            'contact_phone.regex' => 'Введіть, будь ласка, Ваш номер телефону в форматі +380998877665',
            'contact_email.required' => 'Поле "Email" необхідно заповнити',
            'contact_email.email' => 'Введіть, будь ласка, Вашу електронну пошту в форматі example@domain.com',
            'contact_url.url' => 'Введіть, будь ласка, Ваш сайт в форматі http://example.com',
            'cpv_codes_count.in' => "У всіх лотах мають співпадати перші чотири символи коду класифікатора ДК 021:2015",
            'dkpp_codes_count.in' => "У всіх лотах мають співпадати перші п'ять символів коду класифікатора ДК 016:2010 і перші три символи коду класифікатора ДК 021:2015",
        ];
    }

    /**
     * array of lot rules
     * @return array
     */
    public function getLotRules() {
        return [
            'title' => 'required|max:255',
            'description' => 'required',
            'amount' => 'required|numeric',
            'minimal_step' => 'required|numeric',
            'items_count' => 'numeric|min:1'
        ];
    }

    /**
     * array of lot messages
     * @param null $lotIndex
     * @return array
     */
    public function getLotMessages($lotIndex = null) {
        $lotIndexStr = ($lotIndex >= 0) ? 'в лоті '.$lotIndex : '';
        return  [
            'title.required' => "Поле \"Узагальнена назва лоту\" $lotIndexStr повино бути заповнене.",
            'title.max' => "Максимальна довжина поля \"Узагальнена назва лоту\" $lotIndexStr не повинна перевижувати 255 символів",
            'description.required' => "Поле \"Примітки лоту\" $lotIndexStr необхідно заповнити",
            'amount.required' => 'Поле "Очікувана вартість предмета закупівлі" '.$lotIndexStr.' потрібно заповнити',
            'amount.numeric' => 'Поле "Очікувана вартість предмета закупівлі" '.$lotIndexStr.' повинно бути числом.',
            'minimal_step.required' => 'Поле "Розмір мінімального кроку пониження ціни" '.$lotIndexStr.' потрібно заповнити',
            'minimal_step.numeric' => 'Поле "Розмір мінімального кроку пониження ціни" '.$lotIndexStr.' повинно бути числом.',
            'items_count.min' => "Лот $lotIndex повинен містити хоча б один предмет закупівлі.",
        ];
    }

    /**
     * array of item rules
     * @param array $itemCodes
     * @return array
     */
    public function getItemRules($itemCodes = []) {
        $rules = [
            'description' => 'required',
            'quantity' => 'required|integer',
            'unit_id' => 'required',
            'cpv' => 'required',
            'codes.1.id' => 'required|integer',
            'delivery_date_start' => 'required|date',
            'delivery_date_end' => 'required|date|after:delivery_date_start',
            'region_id' => 'required|integer',
            'postal_code' => 'required|digits_between:5,5',
            'locality' => 'required',
            'delivery_address' => 'required'
        ];

        foreach (array_keys($itemCodes) as $index)
            $rules['codes.'.$index.'.id'] = 'required|integer';

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
        $lotIndexStr = ($lotIndex >= 0 && $itemIndex >= 0) ? "Лот $lotIndex, товар $itemIndex. " : '';
        $messages = [
            'description.required' => $lotIndexStr . "Поле \"Конкретна назва предмета закупівлі\" повинно бути заповнене",
            'quantity.required' => $lotIndexStr . "Поле \"Кількість\" повинно бути заповнене",
            'quantity.integer' => $lotIndexStr . "Поле \"Кількість\" повинно бути цілим числом",
            'unit_id.required' => $lotIndexStr . "Поле \"Одиниця виміру\" повинно бути заповнене",
            'cpv.required' => $lotIndexStr . "Поле \"Класифікатор ДК 021:2015\" повинно бути заповнене",
            'codes.1.id.required' => $lotIndexStr . "\"Класифікатор ДК 021:2015\" необхідно вибрати зі списку. Введіть в поле перші цифри коду для отримання підказки",
            'delivery_date_start.required' => $lotIndexStr . "Поле \"Період доставки з\" повинно бути заповнене",
            'delivery_date_start.date' => $lotIndexStr . "Поле \"Період доставки з\" повинно бути датою",
            'delivery_date_end.required' => $lotIndexStr . "Поле \"Період доставки по\" повинно бути заповнене",
            'delivery_date_end.date' => $lotIndexStr . "Поле \"Період доставки по\" повинно бути датою",
            'delivery_date_end.after' => $lotIndexStr . "Поле \"Період доставки по\" повинно бути пізніше ніж \"Період доставки з\"",
            'region_id.required' => $lotIndexStr . "Поле \"Регіон\" повинно бути заповнене",
            'region_id.integer' => $lotIndexStr . "Виберіть \"Регіон\" з випадаючого списку",
            'postal_code.required' => $lotIndexStr . "Поле \"Поштовий індекс\" повинно бути заповнене",
            'postal_code.digits_between' => $lotIndexStr . "Довжина \"Поштового індексу\" повинна бути 5 цифр",
            'locality.required' => $lotIndexStr . "Поле \"Населений пункт\" повинно бути заповнене",
            'delivery_address.required' => $lotIndexStr . "Поле \"Поштова адреса\" повинно бути заповнене"
        ];

        foreach (array_keys($itemCodes) as $index)
            if ($index != 1)
                $messages['codes.'.$index.'.id.required'] = $lotIndexStr . '"Додатковий класифікатор" необхідно вибрати зі списку. Введіть в поле перші цифри коду для отримання підказки';

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
