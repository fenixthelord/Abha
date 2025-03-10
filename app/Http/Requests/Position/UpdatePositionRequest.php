<?php

namespace App\Http\Requests\Position;

use App\Http\Traits\ResponseTrait;
use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
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
        try {
            $rules = [
                'id' => 'required|uuid|exists:positions,id,deleted_at,NULL',
                'name' => 'required|array',
                'name.en' => [
                    'required',
                    'string',
                    Rule::unique('positions', 'name->en')->ignore($this->id),
                ],
                'name.ar' => [
                    'required',
                    'string',
                    Rule::unique('positions', 'name->ar')->ignore($this->id),
                ],
            ];

            if ($this->id !== Position::MASTER_ID) {
                $rules['parent_id'] = [
                    'required',
                    'uuid',
                    'exists:positions,id,deleted_at,NULL',
                    function ($attribute, $value, $fail) {

                        if ($value === $this->id) {
                            $fail('Position cannot be a parent of itself.');
                        }
                        try {
                            $childrenIDs = Position::getChildrenIds($this->id);
                            if (in_array($value, $childrenIDs)) {
                                $fail('Position cannot be a parent of its child.');
                            }
                        } catch (\Exception $e) {
                            $fail($e->getMessage());
                        }
                    }
                ];
            }

            return $rules;
        } catch (\Exception $e) {
            return $this->returnValidationError($e);
        }
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
