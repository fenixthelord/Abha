<?php
namespace App\Http\Controllers\Api\Type;

use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormResource;
use App\Models\Forms\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;

class TypeCustomerController extends Controller {
    use ResponseTrait;

    public function getFormsWithFields(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'type_id' => ['required', 'exists:form_types,id'],
            ], [
                'type_id.required' => __('validation.custom.type_controller.type_id_required'),
                'type_id.exists' => __('validation.custom.type_controller.type_id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $forms = Form::where('form_type_id', $request->type_id)
                ->with('fields')
                ->get();

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
