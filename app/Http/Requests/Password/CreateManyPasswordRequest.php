<?php

namespace App\Http\Requests\Password;

use App\Http\Requests\JSONRequest;

class CreateManyPasswordRequest extends JSONRequest
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
            'list' => 'required|array',
            'list.*.name' => 'required|string',
            'list.*.url' => 'string|nullable',
            'list.*.login' => 'string|nullable',
            'list.*.password' => 'string|nullable',
            'list.*.description' => 'string|nullable',
            'list.*.folderName' => 'string|nullable'
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
            'list.required' => 'list-required',
            'list.array' => 'must-be-array',
            'list.*.name.required' => 'name-required',
            'list.*.name.string' => 'must-be-string',
            'list.*.url.string' => 'must-be-string',
            'list.*.login.string' => 'must-be-string',
            'list.*.password.string' => 'must-be-string',
            'list.*.description.string' => 'must-be-string',
        ];
    }
}
