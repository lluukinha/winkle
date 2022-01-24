<?php

namespace App\Http\Requests\Password;

use App\Http\Requests\JSONRequest;

class UpdatePasswordRequest extends JSONRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'url' => 'string',
            'login' => 'string',
            'password' => 'string',
            'description' => 'string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.string' => 'must-be-string',
            'url.string' => 'must-be-string',
            'login.string' => 'must-be-string',
            'password.string' => 'must-be-string',
            'description.string' => 'must-be-string',
        ];
    }
}
