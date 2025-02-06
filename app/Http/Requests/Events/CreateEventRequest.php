<?php

namespace App\Http\Requests\Events;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class   CreateEventRequest extends FormRequest
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
            "service_id" => ["required", "uuid", "exists:services,id,deleted_at,NULL"],
            "form_id" => ["required",   "uuid",   "exists:forms,id,deleted_at,NULL"],
            "name" => ["required", "array", "max:2" , "min:2"],
            "name.ar" => ["required", "string", "max:255"],
            "name.en" => ["required", "string", "max:255"],
            "details" => ["required", "array", "max:2" , "min:2"],
            "details.ar" => ["required", "string", "max:255"],
            "details.en" => ["required", "string", "max:255"],
            "start_date" => ["required", "date", "after_or_equal:today"],
            "end_date" => ["required", "date", "after_or_equal:start_date"],
            "image" => ["required", "string", "max:255"],
            "file" => ["required" , "string" , "max:255"]
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
