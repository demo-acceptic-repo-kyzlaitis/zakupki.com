<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateUserRequest extends Request {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;//пока логика в контроллере но можно и сюда перенести
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'email'     => 'required|email|unique:users',
            'password'  => 'required|confirmed',
            'name'      => 'required',
            'agreement'      => 'required',
        ];
    }
}
