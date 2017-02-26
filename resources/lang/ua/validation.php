<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => '":attribute" не може бути раніше ніж ":date".',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'Підтвердження паролю не співпадає',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'email'                => 'Введіть, будь ласка, Ваш email в форматі user@domain',
    'exists'               => 'The selected :attribute is invalid.',
    'filled'               => 'The :attribute field is required.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute повинно бути цілим числом.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'               => 'Поле :attribute повинно бути числом.',
    'regex'                => 'The :attribute format is invalid.',
    'required'              => 'Поле ":attribute" необхідно заповнити.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'Користувач з таким ":attribute" уже зареєстрований.',
    'u_r_l'                  => 'Поле ":attribute" повинно мати правильний формат посилання.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'identifier' => [
            'unique' => 'Користувач з таким кодом ЄДРПОУ вже зареєстрований.',
            'numeric' => 'Код ЄДРПОУ повинен бути числом.',
            'digits_between'       => 'Код ЄДРПОУ повинен містити від :min до :max символів.',
        ],
        'contact_url' => [
            'regex' => 'Підтримуються лише протоколи HTTP и HTTPS'
        ],
        'agreement' => [
            'required' => 'Необхідно прийняти згоду суб\'єкта персональних даних.'
        ],
        'features_amount' => [
            'max' => 'Сума нецінових критерії не повина перевищувати 30%'
        ],
        'postal_code' => [
            'digits_between' => 'Індекс повинен містити 5 цифр',
            'numeric' => 'Індекс повинен містити 5 цифр',
        ],
        'contact_phone' => [
            'numeric' => 'Телефон повинен містити лише цифри, наприклад, +380501234567',
        ],
        'resolution' => [
            'min' => 'Відповідь на вимогу повинна містити не менше :min символів.'
        ],


    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'title' => 'Назва',
        'description' => 'Опис',
        'enquiry_start_date' => 'Початок періоду уточнень',
        'enquiry_end_date' => 'Кінець періоду уточнень',
        'tender_start_date' => 'Початок періоду пропозицій',
        'tender_end_date' => 'Кінець періоду пропозицій',

        'identifier ' => 'Код ЄДРПОУ',
        'contact_email' => 'Email',
        'contact_url' => 'Сайт',

    ],

];
