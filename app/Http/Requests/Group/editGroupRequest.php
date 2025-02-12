<?php

namespace App\Http\Requests\Group;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class editGroupRequest extends FormRequest
{ use ResponseTrait;
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
            'group_id' => 'required|uuid',
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string',
            'name.ar' => 'sometimes|string',
            'description' => 'sometimes|array',
            'description.en' => 'sometimes|string',
            'description.ar' => 'sometimes|string',
            'icon' => 'sometimes|string',
            'type' => 'sometimes|string',
            'owner_id' => 'sometimes|uuid',
            'department_id' => 'nullable|uuid',
            'group_service' => 'sometimes|string',
            'user_id' => 'sometimes|array',


        ];
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
