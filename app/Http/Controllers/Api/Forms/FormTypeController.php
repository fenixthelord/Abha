<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\StoreFormTypeRequest;
use App\Http\Requests\Forms\UpdateFormTypeRequest;
use App\Http\Resources\Forms\FormResource;
use App\Http\Resources\Forms\FormTypeResource;
use App\Http\Traits\HasPermissionTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\FormType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormTypeController extends Controller
{
    use ResponseTrait, HasPermissionTrait;


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $this->authorizePermission('formtype.show');

            $pageNumber = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $formTypes = FormType::with('forms')->paginate($perPage, ['*'], 'page', $pageNumber);
            $data['form_types'] = FormTypeResource::collection($formTypes);

            return $this->PaginateData($data, $formTypes);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->authorizePermission('formtype.store');

            $formType = FormType::create($request->validated());
            $data['form'] = FormTypeResource::make($formType);
            DB::commit();
            return $this->returnData($data, "Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        try {
            $this->authorizePermission('formtype.show');

            $validate = Validator::make(request()->all(), [
                'id' => 'required|string|exists:form_types,id',
            ]);
            if ($validate->fails()) {
                return $this->returnValidationError($validate);
            }
            $id = request()->input('id');
            $formType = FormType::with('forms')->findOrFail($id);
            $data['form_type'] = FormTypeResource::make($formType);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFormTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->authorizePermission('formtype.update');

            $id = $request->input('id');
            $formType = FormType::findOrFail($id);
            $formType->update([
                'name' => $request->name,
            ]);

            $data['form'] = FormTypeResource::make($formType);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        DB::beginTransaction();
        try {
            $this->authorizePermission('formtype.delete');

            $validate = Validator::make(request()->all(), [
                'id' => 'required|string|exists:form_types,id',
            ]);
            if ($validate->fails()) {
                return $this->returnValidationError($validate);
            }
            $id = request()->input('id');
            $formType = FormType::findOrFail($id);
            if (!$formType || $formType->trashed()) {
                return $this->badRequest('Form already deleted.');
            }
            $formType->name = $formType->name . '-' . $formType->id . '-deleted';
            $formType->delete();
            DB::commit();
            return $this->returnSuccessMessage('Form deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());

        }

    }
}
