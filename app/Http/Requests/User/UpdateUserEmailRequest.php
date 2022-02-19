<?php

namespace App\Http\Requests\User;

use App\Http\Requests\JSONRequest;

class UpdateUserEmailRequest extends JSONRequest
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
            'email' => 'required|email',
            'confirmEmail' => 'required|email',
            'password' => 'required|string',
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
            'email.required' => 'is-required',
            'email.email' => 'must-be-email',
            'confirmEmail.required' => 'is-required',
            'confirmEmail.email' => 'must-be-email',
            'password.required' => 'is-required',
            'password.string' => 'is-empty'
        ];
    }
}
