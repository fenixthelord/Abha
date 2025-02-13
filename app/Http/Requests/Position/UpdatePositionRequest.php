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
        return [
            "id" => "required|uuid|exists:positions,id,deleted_at,NULL",
            "parent_id" => [
                "required",
                "uuid",
                "exists:positions,id,deleted_at,NULL",
                function ($attribute, $value, $fail) {
                    if ($value === $this->id) {
                        $fail('Position cannot be a parent of itself.');
                    }
                    $chieldsID = Position::getChildrenIds($this->id);
                    if (in_array($value, $chieldsID)) {
                        $fail('Position cannot be a parent of his child.');
                    }
                }
            ],
            "name" => "required|array",
            "name.en" => ["required", "string", Rule::unique('positions', 'name->en')->ignore($this->id)],
            "name.ar" => ["required", "string", Rule::unique('positions', 'name->ar')->ignore($this->id)],
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
