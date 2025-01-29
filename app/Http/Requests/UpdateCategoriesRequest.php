<?php

namespace App\Http\Requests;

use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCategoriesRequest extends FormRequest
{
    use ResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_uuid' => [
                'required', // Ensure field is present
                'uuid',     // Ensure valid UUID format
                Rule::exists('departments', 'uuid')->where('deleted_at', null), // Ensure exists in DB
            ],
            'chields' => 'required|array',
            'chields.*.uuid' => 'required|uuid|exists:categories,uuid',
            'chields.*.name' => 'required|string',
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
            // $uuids = [];
            // $this->collectUuids($this->input('chields'), $uuids);

            // Check for duplicate UUIDs in request
            // if (count($uuids) !== count(array_unique($uuids))) {
            //     $validator->errors()->add('chields', 'Duplicate UUIDs found in the structure.');
            //     return;
            // }
            // dd($validator->validate()->errors()->first());
            // if ($validator->validate()->errors())

            // Validate name uniqueness and parent relationships
            // $this->validateFirstChields(
            //     $validator,
            //     $this->input('chields'),
            //     $this->input('department_uuid'),
                
            // );
    //     });
    // }

    // protected function collectUuids($chields, &$uuids)
    // {
        // foreach ($chields as $child) {
        //     // Ensure the 'uuid' key exists before accessing it
        //     if (!isset($child['uuid'])) {
        //         continue; // Skip this child if 'uuid' is missing
        //     }

        //     $uuids[] = $child['uuid'];

        //     // Recursively collect UUIDs from nested children
        //     if (isset($child['chields']) && is_array($child['chields'])) {
        //         $this->collectUuids($child['chields'], $uuids);
        //     }
        // }
    // }

    // protected function validateFirstChields($validator, $chields, $department_uuid ) {
    //     $department = Department::where('uuid', $department_uuid)->first();
    //     if (!$department) {
    //         $validator->errors()->add('department_uuid', 'Department not found.');
    //         return;
    //     }

    //     foreach ($chields as $index => $child) {
    //         // Ensure the 'uuid' key exists before accessing it
    //         if (!isset($child['uuid'])) {
    //             $validator->errors()->add("chields.$index.uuid", 'UUID is required for all items.');
    //             return;
    //         }

    //         // Check name uniqueness among siblings
    //         $exists = Category::where('department_id', $department->id)
    //             ->where('name', $child['name'])
    //             ->where('uuid', '!=', $child['uuid'])
    //             ->exists();

    //         if ($exists) {
    //             $validator->errors()->add("chields.$index.name", 'Name must be unique in this hierarchy level.');
    //             return;
    //         }

    //         // Recursively validate children
    //         if (isset($child['chields']) && is_array($child['chields'])) {
    //             // $this->validateChields($validator, $child['chields'], $child['uuid']);
    //         }
    //     }
    // }
    // protected function validateChields($validator, $chields, $parentUuid )
    // {
    //     foreach ($chields as $index => $child) {
    //         // Ensure the 'uuid' key exists before accessing it
    //         if (!isset($child['uuid'])) {
    //             $validator->errors()->add("chields.$index.uuid", 'UUID is required for all items.');
    //             return;
    //         }

    //         $parent = Category::where('uuid', $parentUuid)->first();
    //         // dd($parentUuid);
    //         if (!$parent) {
    //             $validator->errors()->add("chields.$index.parent", 'Parent department not found.');
    //             return;

    //         }

    //         // Check name uniqueness among siblings
    //         $exists = Category::where('parent_id', $parent->id)
    //             ->where('name', $child['name'])
    //             ->where('uuid', '!=', $child['uuid'])
    //             ->exists();

    //         if ($exists) {
    //             $validator->errors()->add("chields.$index.name", 'Name must be unique in this hierarchy level.');
    //             return;
    //         }

    //         // Recursively validate children
    //         if (isset($child['chields']) && is_array($child['chields'])) {
    //             $this->validateChields($validator, $child['chields'], $child['uuid']);
    //         }
    //     }
    // }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}