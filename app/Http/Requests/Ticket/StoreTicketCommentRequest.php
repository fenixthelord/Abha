<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketCommentRequest extends FormRequest
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
            'ticket_id' => 'required|exists:tickets,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'mentions' => 'nullable|array',
            'mentions.*.type' => 'required|string|in:user,department,position',
            'mentions.*.identifier' => 'required|string',
            'mentions.*.id' => 'nullable|string', // Store only if available
        ];
    }
}
