<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormTypeRequest extends FormRequest
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
            'name' => 'required|array|min:2|max:2',
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('forms', 'name->en')
                    ->ignore($this->route('form'))
            ],
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('forms', 'name->ar')
                    ->ignore($this->route('form'))
            ],
        ];
    }
}
