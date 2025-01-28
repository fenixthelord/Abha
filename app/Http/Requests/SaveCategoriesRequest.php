<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SaveCategoriesRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department_uuid' => $this->department_uuid,
            "chields" => $this->chields,
        ]);
    }

    public function rules()
    {
        // dd($this->department_uuid);
        return [
            'department_uuid' => [
                'required', // Ensure field is present
                'uuid',     // Ensure valid UUID format
                Rule::exists('departments', 'uuid')->where('deleted_at' , null), // Ensure exists in DB
            ],
            'chields' => 'array',
            'chields.*.uuid' => 'nullable|uuid', // Existing categories
            'chields.*.name' => 'required_without:chields.*.uuid|string|max:255',
            'chields.*.chields' => 'sometimes|array',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->has('department_uuid')) {
                return;
            }

            $department = Department::where('uuid', $this->department_uuid)->firstOrFail();

            // Validate department name remains unchanged
            if ($department->department_name !== $this->department_name) {
                $validator->errors()->add('department_name', 'Department name cannot be modified');
            }

            $this->validateCategories($validator, $department->id, $this->chields);
        });
    }

    private function validateCategories($validator, $departmentId, $categories, $parentId = null)
    {
        $existingNames = Category::where('department_id', $departmentId)
            ->where('parent_id', $parentId)
            ->pluck('name', 'uuid')
            ->toArray();

        $requestNames = [];

        foreach ($categories as $index => $category) {
            $path = "chields.$index";
            // Validate existing categories
            if (!empty($category['uuid'])) {
                $existingCategory = Category::where('uuid', $category['uuid'])
                    ->where('department_id', $departmentId)
                    ->first();

                if (!$existingCategory) {
                    $validator->errors()->add("$path.uuid", 'Invalid category UUID');
                    continue;
                }

                // Verify name matches existing record
                if ($existingCategory->name !== $category['name']) {
                    $validator->errors()->add("$path.name", 'Category name cannot be modified');
                }

                // Verify parent hierarchy matches
                if ($existingCategory->parent_id != $parentId) {
                    $validator->errors()->add("$path.uuid", 'Category hierarchy cannot be modified');
                }
            }
            // Validate new categories
            else {
                // Check against existing names in DB
                if (in_array($category['name'], $existingNames)) {
                    $validator->errors()->add("$path.name", "Category name '{$category['name']}' already exists in this level");
                }

                // Check against names in current request level
                if (in_array($category['name'], $requestNames)) {
                    $validator->errors()->add("$path.name", "Duplicate category name '{$category['name']}' in same level");
                }

                $requestNames[] = $category['name'];
            }

            // Recursively validate children
            if (!empty($category['chields'])) {
                $parentUuid = $category['uuid'] ?? null;
                $this->validateCategories(
                    $validator,
                    $departmentId,
                    $category['chields'],
                    $parentUuid ? Category::where('uuid', $parentUuid)->value('id') : null
                );
            }
        }
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
