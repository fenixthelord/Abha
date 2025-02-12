<?php

namespace App\Http\Requests\Group;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddGroupRequest extends FormRequest
{  use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.en' => 'required|string|',
            'name.ar' => 'required|string|',
            'description' => 'required|array',
            'description.en' => 'required|string',
            'description.ar' => 'required|string',
            'icon' => 'string',
            'type' => 'required|string',
            'member_type' => 'required|string',

            'department_id' => 'uuid|exists:departments,id',

            'user_id' => 'required|array',
            //
        ];
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
