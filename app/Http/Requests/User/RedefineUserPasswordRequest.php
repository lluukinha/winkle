<?php

namespace App\Http\Requests\User;

use App\Http\Requests\JSONRequest;

class RedefineUserPasswordRequest extends JSONRequest
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
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string',
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
            'password.required' => 'is-required',
            'password.string' => 'must-be-string',
            'password.min' => 'must-be-at-least-6',
            'confirmPassword.required' => 'is-required',
            'confirmPassword.string' => 'must-be-string',
        ];
    }
}
