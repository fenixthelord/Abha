<?php

namespace App\Http\Requests\Forms;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateFormBuilderRequest extends FormRequest
{
    use ResponseTrait;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'numeric', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => 'required|array|min:2|max:2',
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('forms', 'name->en')
                    ->ignore($this->route('form'))
                    ->where("category_id", $this->category_id)
            ],
            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('forms', 'name->ar')
                    ->ignore($this->route('form'))
                    ->where("category_id", $this->category_id)
            ],

            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|array|min:2|max:2',
            'fields.*.label.en' => 'required|string|max:255',
            'fields.*.label.ar' => 'required|string|max:255',
            'fields.*.placeholder' => 'required|array|min:2|max:2',
            'fields.*.placeholder.en' => 'required|string|max:255',
            'fields.*.placeholder.ar' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,number,date,dropdown,radio,checkbox,file,map',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.order' => 'required|numeric',
            'fields.*.options'  => 'nullable|array', // For dropdown, radio, and checkbox types
            'fields.*.options.*.label' => 'required|array|min:2|max:2',
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
            'name.en.unique' => 'The English name already exists in this category.',
            'name.required.ar' => 'The Arabic name already exists in this category.',
            'fields.required' => 'The form fields is required.',
            'fields.*.label.required' => 'Each form field must have a label.',
            'fields.*.label.en.required' => 'Each form field must have an English label.',
            'fields.*.label.ar.required' => 'Each form field must have an Arabic label.',
            'fields.*.placeholder.required' => 'Each form field must have a placeholder.',
            'fields.*.placeholder.en.required' => 'Each form field must have an English placeholder.',
            'fields.*.placeholder.ar.required' => 'Each form field must have an Arabic placeholder.',
            'fields.*.type.in' => 'Invalid field type. Allowed types: text, number, date, dropdown, radio, checkbox, file, map.',
            'fields.*.order.required' => 'Each form field must have an order.',
            'fields.*.order.numeric' => 'Each form field must have an order as number.',
            'fields.*.options.*.label.required' => 'Each field option must have a label.',
            'fields.*.options.*.label.en.required' => 'Each field option must have an English label.',
            'fields.*.options.*.label.ar.required' => 'Each field option must have an Arabic label.',
            'fields.*.options.*.order.required' => 'Each field option must have an order.',
            'fields.*.options.*.order.numeric' => 'Each field option must have an order as number.',
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
