<?php

namespace App\Http\Controllers\Api\Type;

use App\Http\Controllers\Controller;

use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Forms\FormResource;
use App\Http\Resources\Forms\SubmissionResource;
use App\Models\Forms\Form;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\FormSubmission;

class CustomerTypeController extends Controller {
    use ResponseTrait;
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    public function getCustomersByType(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1'],
                'type_id' => ['nullable', 'exists:types,id'],
                'search' => ['nullable', 'string'],
            ], [
                'type_id.exists' => __('validation.custom.type_controller.type_not_found'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $data = [
                'type_id' => $request->type_id,
                'search' => $request->input('search'),
                'page' => $request->input('page'),
                'per_page' => $request->input('per_page'),
            ];

            $response = $this->customerService->getCall('service/customers', $data);
            $responseData = json_decode(json_encode($response));

            if (isset($responseData->error)) {
                return $this->returnError($responseData->error);
            }

            // return $responseData;
            $customersCollection = CustomerResource::collection($responseData->data->customers);
            $data = [
                "customers"      => $customersCollection,
                "current_page"   => $responseData->current_page ?? null,
                "next_page"      => $responseData->next_page ?? null,
                "previous_page"  => $responseData->previous_page ?? null,
                "total_pages"    => $responseData->total_pages ?? null,
            ];

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getFormsWithFields(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'type_id' => ['required', 'exists:types,id'],
            ], [
                'type_id.required' => __('validation.custom.type_controller.type_required'),
                'type_id.exists' => __('validation.custom.type_controller.type_not_found'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $forms = Form::whereHas("types", function ($query) use ($request) {
                $query->where("id", $request->type_id);
            })->with("fields")->get();

            if ($forms->isEmpty()) {
                return $this->NotFound(__('validation.custom.type_controller.forms_not_found'));
            }

            return $this->returnData([
                'forms' => FormResource::collection($forms)
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getFormSubmissionValues(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'form_submission_id' => ['required', 'exists:form_submissions,id'],
            ], [
                'form_submission_id.exists' => __('validation.custom.type_controller.form_submission_not_found'),
                'form_submission_id.required' => __('validation.custom.type_controller.form_submission_required'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $data = [
                'form_submission_id' => $request->form_submission_id,
            ];

            $response = $this->customerService->getCall('service/get-status', $data);
            $responseData = json_decode(json_encode($response['data']));

            if (isset($responseData->error)) {
                return $this->returnError($responseData->error);
            }

            $formSubmissionValues = FormSubmission::with('values')
                ->where('id', $request->form_submission_id)
                ->get();

            return $this->returnData([
                'form_submission_id' => $request->form_submission_id,
                'submission' =>  SubmissionResource::collection($formSubmissionValues)
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function updateStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'form_submission_id' => ['required', 'exists:form_submissions,id'],
                'status' => ['required', 'in:0,1,2'],
                'reason' => ['nullable', 'string'],
            ], [
                'form_submission_id.exists' => __('validation.custom.type_controller.form_submission_not_found'),
                'form_submission_id.required' => __('validation.custom.type_controller.form_submission_required'),
                'status.required' => __('validation.custom.type_controller.status_required'),
                'status.in' => __('validation.custom.type_controller.status_invalid'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $form_submission_id = $request->form_submission_id;
            $status = $request->status;
            $reason = $request->reason;

            $data = [
                'form_submission_id' => $form_submission_id,
                'status' => $status,
                'reason' => $reason
            ];

            $response = $this->customerService->postCall('service/status', $data);
            $responseData = json_decode(json_encode($response));

            if (isset($responseData->error)) {
                return $this->returnError($responseData->error);
            }

            return $this->returnSuccessMessage(__('validation.custom.type_controller.status_updated'));
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function deleteCustomersByType(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required'],
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $data = [
                'id' => $request->id
            ];

            $response = $this->customerService->deleteCall('service/delete', $data);
            $responseData = json_decode(json_encode($response));

            if (isset($responseData->error)) {
                return $this->returnError($responseData->error);
            }

            return $this->returnSuccessMessage(__('validation.custom.type_controller.customer_type_deleted'));
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
