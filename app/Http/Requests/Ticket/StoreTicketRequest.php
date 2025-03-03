<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\ResponseTrait;


class StoreTicketRequest extends FormRequest
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
                "name" => ["required", "array"],
                "name.en" => ["required", "string", "min:2", "max:255"],
                "name.ar" => ["required", "string", "min:2", "max:255"],
                "department_id" => "required|exists:departments,id",
                "category_id" => "required|exists:categories,id",
                "parent_id" => "nullable|exists:tickets,id",
        ];
    }
    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
