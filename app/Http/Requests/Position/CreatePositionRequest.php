<?php

namespace App\Http\Requests\Position;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreatePositionRequest extends FormRequest
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
            "parent_id" => "required|uuid|exists:positions,id",
            "name" => "required|array",
            "name.en" => ["required", "string", Rule::unique("positions", "name->en")],
            "name.ar" => ["required", "string", Rule::unique("positions", "name->en")],
        ];
    }

    // protected function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {

    //         $chields = $this->input('chields', []);
    //         $this->validateChields($validator, $chields, 'chields');
    //     });
    // }}

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
