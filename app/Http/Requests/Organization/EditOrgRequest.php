<?php

namespace App\Http\Requests\Organization;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditOrgRequest extends FormRequest
{   use ResponseTrait;
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
                'org_uuid' => ['required', Rule::exists('organizations', 'uuid')->where("deleted_at", null)],
                'department_uuid' => [Rule::exists('departments', 'uuid')->where("deleted_at", null)],
                'manger_uuid' => [Rule::exists('users', 'uuid')->where("deleted_at", null)],
                'employee_uuid' => [Rule::exists('users', 'uuid')->where("deleted_at", null)],
                'position.en' => 'string',
                'position.ar' => 'string',
                'position' => 'array'

            //
        ];
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));

}
}
