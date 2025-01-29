<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
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
            'department_uuid' => 'required|uuid|exists:departments,uuid',
            'chields' => 'nullable|array',
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
        $names = [];
        foreach ($chields as $index => $child) {
            $currentPath = "{$path}.{$index}";

            // Validate child structure
            $childValidator = Validator::make($child, [
                'name' => 'required|string',
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