<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormSubmissionResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\Forms\FormSubmission;
use App\Models\Forms\FormSubmissionValue;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $permissions = [
            'store'  => ['formsubmission.create', 'formsubmissionvalue.create'],
        ];

        foreach ($permissions as $method => $permissionGroup) {
            foreach ($permissionGroup as $permission) {
                $this->middleware("permission:{$permission}")->only($method);
            }
        }
    }
    public function showFormWithSubmissions($id)
    {
        $formField = Form::with('type', 'submissions.values.field')->findOrFail($id);
        return FormSubmissionResource::make($formField);
    }

    public function store(Request $request, $id)
    {
        try {
            $form = Form::findOrFail($id);
            $rules = [];

            // Create validation rules dynamically
            foreach ($form->fields as $field) {
                if ($field->required) {
                    $rules[$field->label] = ['required'];
                }
                if ($field->type === 'number') {
                    $rules[$field->label][] = 'numeric';
                }
                if ($field->type === 'file') {
                    $rules[$field->label][] = 'file';
                }
                if ($field->type === 'date') {
                    $rules[$field->label][] = 'date';
                }
            }
            //Validate $request based on the created rules
            $validatedData = $request->validate($rules);


//            $request->merge([
//                'submitter_service' => $request->submitter_service ?? 'user', // Default to "user" if not provided
//            ]);
//            $request->validate([
//
//                'submitter_service' => 'in:customer,user', // Ensure it's either "customer" or "user"
//                'submitter_id' => [
//                    'required_if:submitter_service,customer', // Required if service is "customer"
//                    'nullable'
//                    // Ensure ID exists in the customers table
//                ],
//            ]);
//            $submitterId = ($request->submitter_service === 'user')
//                ? auth()->id()
//                : $request->submitter_id;

            $form_submission_validated_data = $request->validate([
                'submitter_id' => ['nullable', 'required_with:submitter_service'],
                'submitter_service' => ['nullable', 'string', 'max:255', 'required_with:submitter_id'],
            ]);

            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'submitter_id' => $form_submission_validated_data['submitter_id']?? auth()->id(),
                'submitter_service' => $form_submission_validated_data['submitter_service']?? null,
            ]);

            // Save Field Values
            foreach ($form->fields as $field) {
                $value = $validatedData[$field->label];
                $field = FormField::find($field->id);

                if ($field) {
                    FormSubmissionValue::create([
                        'form_submission_id' => $submission->id,
                        'form_field_id' => $field->id,
                        'value' => is_array($value) ? json_encode($value) : $value,
                    ]);
                } else {
                    // Handle non-existent form field
                    return $this->badRequest( "Form field with the ID you provided does not exist.");
                }
            }

            return $this->returnSuccessMessage('Form submitted successfully');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
