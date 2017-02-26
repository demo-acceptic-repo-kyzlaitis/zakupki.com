<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Model\Country;
use App\Model\Organization;
use App\Model\TendersRegions;
use Illuminate\Support\Facades\Auth;

class CreateOrganizationRequest extends Request
{



    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $data = $this->request->all();

        $rules = [
            'name'           => 'required',
            'organization_identifier'     => 'required',
            'country_iso'     => 'required',
            'region_id'      => 'required',
            'locality'       => 'required',
            'street_address' => 'required',
            'postal_code'    => 'required|numeric|digits_between:5,5',
            'legal_name'  => 'required',
            'legal_name_en'  => 'required',
//            'contact_name_en'=> 'required',
//            'contact_name'   => 'required',
//            'contact_email'  => 'required|email',
//            'contact_phone'  => 'required|regex:"\+380\d{3,12}"',
//            'contact_url'    => ['URL', 'regex:/^(http|https)/'],
        ];

        if(isset($data['type'])) {
            $rules['type'] = 'required';
        }


        /**
         * валидация для не админов и для учасников
         */
        if(!Auth::user()->super_user && isset($data['type']) && $data['type'] === 'supplier') {
            $rules['terms'] = 'required';
        }

        if(isset($data['country_iso']) && $data['country_iso'] === 'UA') {
            $rules['organization_identifier'] = 'required|regex:/^(\d{8})(\d{2})?$/';
        }

        if(isset($data['type']) && $data['type'] === 'guest') {
            unset($rules['name']);
            unset($rules['organization_identifier']);
            unset($rules['region_id']);
            unset($rules['locality']);
            unset($rules['postal_code']);
            unset($rules['street_address']);
            unset($rules['contact_url']);
            unset($rules['street_address']);
            unset($rules['country_id']);
        }

        $ina_hash = hashFromArray([
            $data['organization_identifier'],
            $data['name'],
            self::_getAddress($data),
        ]);

        $organization = Organization::where('ina_hash', 'like', $ina_hash)->first();

        if($organization != null) {
            $rules['ina_hash'] = 'unique';
        }


        return $rules;
    }


    public function messages() {
        $data = $this->request->all();

        $messages = [
            'contact_phone.regex'    => 'Введіть, будь ласка, Ваш телефон в форматі +380930000000',
            'contact_phone.required' => 'Поле "Телефон" необхідно заповнити',
            'name.required'          => 'Поле "Назва організації" необхідно заповнити',
            'organization_identifier.required'          => 'Поле "Код ЄДРПОУ" необхідно заповнити',
            'country_iso.required'          => 'Виберіть країну зі списку.',
            'locality.required'          => 'Поле "Населений пункт" необхідно заповнити',
            'street_address.required'          => 'Поле "Поштова адреса" необхідно заповнити',
            'postal_code.digits_between' => 'Поле "Індекс" повинен містити 5 цифр',
            'postal_code.required' => 'Поле "Індекс" необхідно заповнити',
            'legal_name.required' => 'Поле "Повна юридична назва" необхідно заповнити',
            'legal_name_en.required' => 'Поле "Повна юридична назва англійською мовою" необхідно заповнити',
//            'contact_name_en.required' => 'Поле "Контактна особа англійською мовою" необхідно заповнити',
//            'contact_name.required' => 'Поле "Контактна особа" необхідно заповнити',
//            'contact_email.required' => 'Поле "Email" необхідно заповнити',
        ];

        if(isset($data['country_iso']) && $data['country_iso'] === 'UA') {
            $messages['organization_identifier.regex'] = 'Поле "Код ЄДРПОУ" повино складатись з 8 або 10 цифр. ';
        }

        /**
         * сообщение для не админов и для учасников
         */
        if(!Auth::user()->super_user && isset($data['type']) && $data['type'] === 'supplier') {
            $messages['terms.required'] = 'Необхідно підтвердити ознайомлення з положеннями Регламенту і Договору про надання послуг';
        }


        $ina_hash = hashFromArray([
            $data['organization_identifier'],
            $data['name'],
            self::_getAddress($data),
        ]);

        $organization = Organization::where('ina_hash', 'like', $ina_hash)->first();

        if($organization != null) {
            $messages['ina_hash.unique'] = 'Організація з таким же самим кодом ЭДРПОУ та такою ж адресую вже існує';
        }

        return $messages;
    }


    public static function _getAddress($data) {
        $address = [];
        if(!empty($data['postal_code']))    $address[] = $data['postal_code'];
        if(!empty($data['country_iso']))    $address[] = Country::where('country_iso', 'like', $data['country_iso'])->first()->country_name_ua;//nec
        if(!empty($data['region_id']))      $address[] = TendersRegions::find($data['region_id'])->region_ua;
        if(!empty($data['locality']))       $address[] = $data['locality'];
        if(!empty($data['street_address'])) $address[] = $data['street_address'];

        return implode(', ', $address);
    }
}
