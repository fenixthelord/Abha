<?php

namespace App\Http\Requests\Group;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class idGroupRequest extends FormRequest
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
            'group_id'=>'required'
        ];
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
