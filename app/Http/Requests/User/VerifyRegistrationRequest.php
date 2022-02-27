<?php

namespace App\Http\Requests\User;

use App\Http\Requests\JSONRequest;

class VerifyRegistrationRequest extends JSONRequest
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
            'token' => 'required|string',
            'email' => 'required|string',
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
            'token.required' => 'is-required',
            'token.string' => 'must-be-string',
            'email.required' => 'is-required',
            'email.string' => 'must-be-string',
        ];
    }
}
