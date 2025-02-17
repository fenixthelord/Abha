<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\Forms\FormSubmission;
use App\Models\Forms\FormSubmissionValue;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    use ResponseTrait;
    public function index() {}

    public function store(Request $request, $id)
    {
        try {
            $form = Form::findOrFail($id);
            $rules = [];

            // Create validation rules dynamically
            foreach ($form->fields as $field) {
                if ($field->required) {
                    $rules["fields.{$field->label}"] = ['required'];
                }
                if ($field->type === 'number') {
                    $rules["fields.{$field->label}"][] = 'numeric';
                }
                if ($field->type === 'file') {
                    $rules["fields.{$field->label}"][] = 'file';
                }
                if ($field->type === 'date') {
                    $rules["fields.{$field->label}"][] = 'date';
                }
            }
            //Validate $request based on the created rules
            $validatedData = $request->validate($rules);

            // Save Submission
            $submission = FormSubmission::create(['form_id' => $form->id]);

            // Save Field Values
            foreach ($form->fields as $field) {
                $value = $validatedData['fields'][$field->label];
                $field = FormField::find($field->id);

                if ($field) {
                    FormSubmissionValue::create([
                        'form_submission_id' => $submission->id,
                        'form_field_id' => $field->id,
                        'value' => is_array($value) ? json_encode($value) : $value,
                    ]);
                } else {
                    // Handle non-existent form field
                    return response()->json(['error' => "Form field with the ID you provided does not exist."], 400);
                }
            }

            return response()->json(['message' => 'Form submitted successfully']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
