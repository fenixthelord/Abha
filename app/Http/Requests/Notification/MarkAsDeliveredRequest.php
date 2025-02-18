<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class MarkAsDeliveredRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can modify this if you need additional authorization checks.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'notification_id' => 'required', // Check if notification_id exists in the notifications table

        ];
    }

    /**
     * Customize the error messages for failed validation.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'notification_id.required' => 'The notification ID is required.',


        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    protected function failedValidation($validator)
    {
        throw new \HttpResponseException($this->returnValidationError($validator));
    }
}
