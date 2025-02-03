<?php

namespace App\Http\Requests\Forms;

use App\Enums\FormFiledType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CreateFormBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|numeric',
            'name' => 'required|array|min:1|max:1',
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('forms', 'name->en')
                    ->where("category_id", $this->category_id)
            ],
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('forms', 'name->ar')
                    ->where("category_id", $this->category_id)
            ],

            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|array|min:1|max:1',
            'fields.*.label.en' => 'required|string|max:255',
            'fields.*.label.ar' => 'required|string|max:255',
            'fields.*.placeholder' => 'required|array|min:1|max:1',
            'fields.*.placeholder.en' => 'required|string|max:255',
            'fields.*.placeholder.ar' => 'required|string|max:255',
            'fields.*.type' => ['required', new Enum(FormFiledType::class)],
            'fields.*.required' => 'nullable|boolean',
            'fields.*.order' => 'required|numeric',

            'fields.*.options'  => 'nullable|array', // For dropdown, radio, and checkbox types
            'fields.*.options.*.label' => 'required|array|min:1|max:1',
            'fields.*.options.*.label.en' => 'required|string|max:255',
            'fields.*.options.*.label.ar' => 'required|string|max:255',
            'fields.*.options.*.order' => 'required|numeric',
            'fields.*.options.*.selected' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'The category name is required.',
            'name.required' => 'The form name is required.',
            'name.required.en' => 'The form English name is required.',
            'name.required.ar' => 'The form Arabic name is required.',

            'fields.required' => 'The form fields is required.',
            'fields.*.label.required' => 'Each form field must have a label.',
            'fields.*.label.en.required' => 'Each form field must have an English label.',
            'fields.*.label.ar.required' => 'Each form field must have an Arabic label.',
            'fields.*.placeholder.required' => 'Each form field must have a placeholder.',
            'fields.*.placeholder.en.required' => 'Each form field must have an English placeholder.',
            'fields.*.placeholder.ar.required' => 'Each form field must have an Arabic placeholder.',
            'fields.*.type.in' => 'Invalid field type. Allowed types: text, number, date, dropdown, radio, checkbox, file.',
            'fields.*.order.required' => 'Each form field must have an order.',

            'fields.*.options.*.label.required' => 'Each field option must have a label.',
            'fields.*.options.*.label.en.required' => 'Each field option must have an English label.',
            'fields.*.options.*.label.ar.required' => 'Each field option must have an Arabic label.',
            'fields.*.options.*.order.required' => 'Each field option must have an order.',
        ];
    }
}
