<?php

namespace App\Http\Requests\Categories;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
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
            "department_id" => [
                "required",
                Rule::exists("departments", "id")->where("deleted_at", null)

            ],
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
