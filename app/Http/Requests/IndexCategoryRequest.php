<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class IndexCategoryRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_uuid' => [
                'sometimes',
                Rule::exists("departments", "uuid")->where("deleted_at", null)

            ],
            'parent_category_uuid' => [
                'sometimes',
                Rule::exists("categories" , "uuid")->where("deleted_at", null)

            ],
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }


    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
