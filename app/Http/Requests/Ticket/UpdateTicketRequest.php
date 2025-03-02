<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
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
            "id" => ["required", "exists:tickets,id"],
                "name" => ["required", "array"],
                "name.en" => [
                    "required",
                    "string",
                    "min:2",
                    "max:255"
                ],
                "name.ar" => [
                    "required",
                    "string",
                    "min:2",
                    "max:255"
                ],
                'department_id' => 'sometimes|required|exists:departments,id',
                'position_id' => 'sometimes|required|exists:positions,id',
                'parent_id' => 'nullable|exists:tickets,id',
        ];
    }
}
