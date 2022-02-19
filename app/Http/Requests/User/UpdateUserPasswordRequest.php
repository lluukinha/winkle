<?php

namespace App\Http\Requests\User;

use App\Http\Requests\JSONRequest;

class UpdateUserPasswordRequest extends JSONRequest
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
            'password' => 'required|string',
            'newPassword' => 'required|string',
            'confirmNewPassword' => 'required|string',
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
            'password.required' => 'is-required',
            'password.string' => 'must-be-string',
            'newPassword.required' => 'is-required',
            'newPassword.string' => 'must-be-string',
            'confirmNewPassword.required' => 'is-required',
            'confirmNewPassword.string' => 'must-be-string',
        ];
    }
}
