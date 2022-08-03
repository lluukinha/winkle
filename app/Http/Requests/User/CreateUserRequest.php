<?php

namespace App\Http\Requests\User;

use App\Http\Requests\JSONRequest;

class CreateUserRequest extends JSONRequest
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
            'name' => 'required|string',
            'email' => 'required|string',
            'plan' => 'required|int',
            'admin' => 'required|bool',
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
            'email.string' => 'must-be-string',
            'name.required' => 'is-required',
            'email.required' => 'is-required',
            'plan.required' => 'is-required',
            'admin.required' => 'is-required',
            'admin.bool' => 'must-be-bool',
        ];
    }
}
