<?php

namespace App\Http\Requests\Position;

use App\Http\Traits\ResponseTrait;
use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DeletePositionRequest extends FormRequest
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
            'id' => ["nullable", "exists:positions,id,deleted_at,NULL", Rule::notIn([Position::MASTER_ID])]
        ];
    }

    public function messages(): array
    {
        return [
            'id.not_in' => 'Cannot delete master position',
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
