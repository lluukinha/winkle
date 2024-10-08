<?php

namespace App\Http\Requests\Password;

use App\Http\Requests\JSONRequest;

class GetPasswordsFromExtensionRequest extends JSONRequest
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
            'email' => 'required|string',
            'password' => 'required|string',
            'masterPassword' => 'required|string',
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
            'email.required' => 'required',
            'email.string' => 'must-be-string',
            'password.required' => 'required',
            'password.string' => 'must-be-string',
            'masterPassword.required' => 'required',
            'masterPassword.string' => 'must-be-string',
        ];
    }
}
