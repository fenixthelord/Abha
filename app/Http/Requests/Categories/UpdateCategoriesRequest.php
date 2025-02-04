<?php

namespace App\Http\Requests\Categories;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateCategoriesRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    // protected function prepareForValidation(): void
    // {
    //     $this->merge([
    //         'department_uuid' => $this->department_uuid,
    //     ]);
    // }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_uuid' => [
                'required',
                'uuid',
                Rule::exists("departments", "uuid")->where("deleted_at", null)

            ],
            "category_uuid" => [
                'required',
                'uuid',
                Rule::exists("categories", "uuid")->where("deleted_at", null)
            ],
            "name" => ["required", "array"],
            "name.en" => [
                "required",
                "string",
                "min:2",
                "max:255",
                Rule::unique('categories', 'name->en')
                    ->whereNull('parent_id')
                    ->where("department_id", $this->departmentId)
            ],
            "name.ar" => [
                "required",
                "string",
                "min:2",
                "max:255",
                Rule::unique('categories', 'name->ar')
                    ->whereNull('parent_id')
                    ->where("department_id", $this->departmentId),
            ],
            "chields" => ["required", "array"]
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateChields($validator, $this->input('chields', []), 'chields');
        });
    }

    private function validateChields($validator, $chields, $path = 'chields')
    {
        if (empty($chields)) {
            $validator->errors()->add("chields", "The chields array is empty");
            throw new HttpResponseException($this->returnValidationError($validator));
        }
        $namesAR = [];
        $namesEN = [];
        foreach ($chields as $index => $child) {
            $currentPath = "{$path}.{$index}";

            // Validate child structure
            $childValidator = Validator::make($child, [
                'category_uuid' => [
                    'required',
                    'uuid',
                    Rule::exists("categories", "uuid")->where("deleted_at", null)
                ],
                'name' => 'required|array',
                'name.en' => 'required|string|min:2|max:255',
                'name.ar' => 'required|string|min:2|max:255',
                'chields' => 'nullable|array',
            ]);

            if ($childValidator->fails()) {
                foreach ($childValidator->errors()->messages() as $key => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add("{$currentPath}.{$key}", $message);
                        // For Arabic
                        $arabicMessage = __('validation.custom.' . $key, [], 'ar');
                        if ($arabicMessage !== 'validation.custom.' . $key) {
                            $validator->errors()->add("{$currentPath}.{$key}", $arabicMessage);
                        }
                    }
                }
            }

            // Check for duplicate names in this same level
            $nameEN = $child['name']["en"] ?? null;
            $nameAR = $child['name']["ar"] ?? null;
            if ($nameEN !== null) {
                if (in_array($nameEN, $namesEN)) {
                    $validator->errors()->add("{$currentPath}.name.en", "The name '{$nameEN}' must be unique within this level.");
                } else {
                    $namesEN[] = $nameEN;
                }
            }
            if ($nameAR !== null) {
                if (in_array($nameAR, $namesAR)) {
                    $validator->errors()->add("{$currentPath}.name.ar", "The name '{$nameAR}' must be unique within this level.");
                } else {
                    $namesAR[] = $nameAR;
                }
            }

            // Recursively validate children
            if (!empty($child['chields'])) {
                $this->validateChields($validator, $child['chields'], "{$currentPath}.chields");
            }
        }
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
