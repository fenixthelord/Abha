<?php

namespace App\Http\Requests\Categories;

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
            'department_id' => [
                'sometimes',
                Rule::exists("departments", "id")->where("deleted_at", null)

            ],
            'parent_category_id' => [
                'sometimes',
                Rule::exists("categories", "id")->where("deleted_at", null)

            ],
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }


    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
