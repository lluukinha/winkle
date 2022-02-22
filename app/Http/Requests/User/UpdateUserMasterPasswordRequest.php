<?php

namespace App\Http\Requests\User;

use App\Http\Requests\JSONRequest;

class UpdateUserMasterPasswordRequest extends JSONRequest
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
            'oldMasterPassword' => 'required|string',
            'newMasterPassword' => 'required|string|min:6',
            'confirmNewMasterPassword' => 'required|string,
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
            'oldMasterPassword.required' => 'is-required',
            'oldMasterPassword.string' => 'must-be-string',
            'newMasterPassword.required' => 'is-required',
            'newMasterPassword.string' => 'must-be-string',
            'newMasterPassword.min' => 'must-be-at-least-6',
            'confirmNewMasterPassword.required' => 'is-required',
            'confirmNewMasterPassword.string' => 'must-be-string',
        ];
    }
}
