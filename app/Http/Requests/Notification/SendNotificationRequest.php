<?php

namespace App\Http\Requests\Notification;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendNotificationRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to make this request.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [

            'title'          => 'required|string',
            'body'           => 'required|string',
            'user_ids'       => 'nullable|array',
            // Each element in the user_ids array must be a valid UUID.
            'user_ids.*'     => 'required|string',
            'model'          => 'required|string',
            'group_id'       => 'nullable|uuid',
            'image'          => 'nullable|string',
            'url'            => 'nullable|string',

        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [

            'title.required'      => 'The title is required.',
            'title.string'        => 'The title must be a string.',
            'body.required'       => 'The body is required.',
            'body.string'         => 'The body must be a string.',

            'user_ids.array'      => 'The user IDs must be an array.',
//            'user_ids.*.required' => 'Each user ID is required.',
//            'user_ids.*.uuid'     => 'Each user ID must be a valid UUID.',
            'model.required'      => 'The model is required.',
            'model.string'        => 'The model must be a string.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation($validator)
    {
        // Throws an HttpResponseException with a standardized validation error response.
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
