<?php

namespace App\Http\Requests\DeviceToken;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveDeviceTokenRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Adjust the authorization logic if needed.
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
            'token'   => 'required|string',
            'user_id' => 'required|exists:users,id|uuid',
        ];
    }

    /**
     * Custom error messages for validation.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'token.required'   => 'The token field is required.',
            'token.string'     => 'The token must be a valid string.',
            'user_id.required' => 'The user_id field is required.',
            'user_id.uuid'     => 'The user_id must be a valid UUID format.',
            'user_id.exists'   => 'The specified user does not exist.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws HttpResponseException
     */
    public function failedValidation($validator)
    {
        // This method uses the returnValidationError() method from ResponseTrait
        // to return a custom formatted validation error response.
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
