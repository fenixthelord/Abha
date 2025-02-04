<?php

namespace App\Http\Requests\Organization;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddOrgRequest extends FormRequest
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
            'department_uuid' => ['required', Rule::exists('departments', 'uuid')->where('deleted_at', null)],
            'manger_uuid' => ['required', Rule::exists('users', 'uuid')->where('deleted_at', null)],
            'user_uuid' => [ 'required', Rule::exists('users', 'uuid')->where("deleted_at", null)],
            'position' => 'required|array',
            'position.en' => 'required|string',
            'position.ar' => 'required|string',
        ];
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
