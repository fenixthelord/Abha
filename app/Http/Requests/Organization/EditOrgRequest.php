<?php

namespace App\Http\Requests\Organization;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditOrgRequest extends FormRequest
{
    use ResponseTrait;
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
            'org_id' => ['required', 'uuid', Rule::exists('organizations', 'id')->where("deleted_at", null)],
            'department_id' => ['uuid', Rule::exists('departments', 'id')->where("deleted_at", null)],
            'manager_id' => ['uuid', Rule::exists('users', 'id')->where("deleted_at", null)],
            'employee_id' => ['uuid', Rule::exists('users', 'id')->where("deleted_at", null)],
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
