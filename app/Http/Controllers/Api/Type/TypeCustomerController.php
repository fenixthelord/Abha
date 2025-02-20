<?php
namespace App\Http\Controllers\Api\Type;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Forms\FormResource;
use App\Models\Forms\Form;
use App\Models\Forms\FormSubmission;
use App\Models\Forms\FormSubmissionValue;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;

class TypeCustomerController extends Controller {
    use ResponseTrait;
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    public function getCustomersByType(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'type_id' => ['required', 'exists:types,id'],
            ], [
                'type_id.exists' => __('validation.custom.type_controller.type_not_found'),
                'type_id.required' => __('validation.custom.type_controller.type_required'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $data = [
                'type_id' => $request->type_id,
            ];

            $response = $this->customerService->getCall('service/customers', $data);
            $responseData = json_decode(json_encode($response['data']));

            if (isset($responseData->error)) {
                return $this->returnError($responseData->error);
            }


            $customersCollection = CustomerResource::collection($responseData->customers);
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
                'type_id.required' => __('validation.custom.type_controller.type_id_required'),
                'type_id.exists' => __('validation.custom.type_controller.type_id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $forms = Form::whereHas('types', function ($query) use ($request) {
                $query->where('id', $request->type_id);
            })->with('fields')->get();

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
}
