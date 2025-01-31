<?php

namespace App\Http\Requests;

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
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("categories", "name->" .  app()->getLocale())->ignore($this->category_uuid, "uuid")
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
        $names = [];
        foreach ($chields as $index => $child) {
            $currentPath = "{$path}.{$index}";

            // Validate child structure
            $childValidator = Validator::make($child, [
                'category_uuid' => [
                    'required',
                    'uuid',
                    Rule::exists("categories", "uuid")->where("deleted_at", null)
                ],
                "name" => ["required"],
                'chields' => 'nullable|array',
            ]);

            if ($childValidator->fails()) {
                foreach ($childValidator->errors()->messages() as $key => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add("{$currentPath}.{$key}", $message);
                    }
                }
            }

            // Check for duplicate names in this same level
            // Check for duplicate names in this same level
            $name = $child['name'] ?? null;
            if ($name !== null) {
                if (in_array($name, $names)) {
                    $validator->errors()->add("{$currentPath}.name", "The name '{$name}' must be unique within this level.");
                } else {
                    $names[] = $name;
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
