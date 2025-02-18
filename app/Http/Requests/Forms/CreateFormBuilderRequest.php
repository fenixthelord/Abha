<?php

namespace App\Http\Requests\Forms;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateFormBuilderRequest extends FormRequest
{
    use ResponseTrait;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'form_type_id' => 'required|exists:form_types,id',
            'name' => 'required|array|min:2|max:2',
            'name.en' => [
                'required',
                'string',
                'min:2',
                'max:255',

            'name.ar' => [
                'required',
                'string',
                'min:2',
                'max:255',


            'fields' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $has_required_field = collect($value)->contains(function ($field) {
                        return isset($field['required']) && ($field['required'] === true || $field['required'] === 1);
                    });

                    if (!$has_required_field) {
                        $fail('At least one field must have (required: true) set to true.');
                    }
                }
            ],

            'fields.*.label' => 'required|array|min:2|max:2',
            'fields.*.label.en' => 'required|string|max:255',
            'fields.*.label.ar' => 'required|string|max:255',
            'fields.*.placeholder' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,number,date,dropdown,radio,checkbox,file,map',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.order' => 'required|numeric',
            'fields.*.options' => ['nullable', 'array', function ($attribute, $value, $fail) {
                $type = request()->input(str_replace('options', 'type', $attribute));

                if (in_array($type, ['date', 'dropdown', 'radio', 'checkbox']) && (is_null($value) || empty($value))) {
                    return $fail('The options field is required for ' . $type . ' input.');
                }
            }],
            'fields.*.options.*.label' => 'required|array|min:2|max:2',
            'fields.*.options.*.label.en' => 'required|string|max:255',
            'fields.*.options.*.label.ar' => 'required|string|max:255',
            'fields.*.options.*.order' => 'required|numeric',
            'fields.*.options.*.selected' => 'nullable|boolean',
            'fields.*.sources' => ['nullable', 'array', function ($attribute, $value, $fail) {
                $type = request()->input(str_replace('sources', 'type', $attribute));

                if ($type === 'dropdown' && (is_null($value) || empty($value))) {
                    return $fail('The sources field is required when type is dropdown.');
                }
            }],

            'fields.*.sources.*.source_table' => 'required|string|max:255',
            'fields.*.sources.*.source_column' => 'required|string|max:255',
        ]]];
    }

    public function messages(): array
    {
        return [
            'formable_id.required' => 'The formable id is required.',
            'formable_id.uuid' => 'The formable id must be correct uuid.',
            'formable_type.required' => 'The formable type is required.',
            'formable_type.in' => 'Invalid formable type. Allowed types: category,event,employee.',
            'name.required' => 'The form name is required.',
            'name.required.en' => 'The form English name is required.',
            'name.en.unique' => 'The English name already exists in this ' . $this->formale_type . '.',
            'name.required.ar' => 'The Arabic name already exists in this ' . $this->formale_type . '.',
            'fields.required' => 'The form fields is required.',
            'fields.*.label.required' => 'Each form field must have a label.',
            'fields.*.label.en.required' => 'Each form field must have an English label.',
            'fields.*.label.ar.required' => 'Each form field must have an Arabic label.',
            'fields.*.placeholder.required' => 'Each form field must have a placeholder.',
            'fields.*.placeholder.string' => 'Each form field must be a string.',
            'fields.*.type.in' => 'Invalid field type. Allowed types: text, number, date, dropdown, radio, checkbox, file, map.',
            'fields.*.order.required' => 'Each form field must have an order.',
            'fields.*.order.numeric' => 'Each form field must have an order as number.',
            'fields.*.options.required' => 'Each form field must have an options array.',
            'fields.*.options.array' => 'Each form field must have an options as array.',
            'fields.*.options.*.selected.boolean' => 'Each field option must have (selected) as boolean.',
            'fields.*.options.*.label.required' => 'Each field option must have a label.',
            'fields.*.options.*.label.en.required' => 'Each field option must have an English label.',
            'fields.*.options.*.label.ar.required' => 'Each field option must have an Arabic label.',
            'fields.*.options.*.order.required' => 'Each field option must have an order.',
            'fields.*.options.*.order.numeric' => 'Each field option must have an order as number.',
            'fields.*.sources.required' => 'Each form field must have an sources array.',
            'fields.*.sources.array' => 'Each form field must have sources as array.',
            'fields.*.sources.*.source_table.required' => 'Each form field source must have a source_table.',
            'fields.*.sources.*.source_column.required' => 'Each form field source must have a source_column.',
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
