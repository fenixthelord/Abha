<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateCategoriesRequest extends FormRequest
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
            "chields" => [
                "required",
                "array"
            ]
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $chields = $this->input('chields', []);
            $this->validateChields($validator, $chields, 'chields');
        });
    }

    private function validateChields($validator, $chields, $path = 'chields')
    {

        $namesAR = [];
        $namesEN = [];
        foreach ($chields as $index => $child) {
            $currentPath = "{$path}.{$index}";

            // Validate child structure
            if (isset($child["uuid"])) {
                $childValidator = Validator::make($child, [
                    'uuid' => [
                        'sometimes',
                        'string',
                        Rule::exists("categories", "uuid")->where("deleted_at", null)

                    ],
                ]);
            } else {
                $childValidator = Validator::make($child, [
                    'name' => 'required|array',
                    'name.en' => 'required|string|min:2|max:255',
                    'name.ar' => 'required|string|min:2|max:255',
                    'chields' => 'nullable|array',
                ], [
                    'name.required' => 'Category name is required',
                    'name.en.required' => 'English category name is required',
                    'name.ar.required' => 'Arabic category name is required',
                    'name.*.min' => 'Category name must be at least 2 characters',
                    'uuid.exists' => 'Selected category does not exist',
                ]);
            }

            if ($childValidator->fails()) {
                foreach ($childValidator->errors()->messages() as $key => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add("{$currentPath}.{$key}", $message);
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
