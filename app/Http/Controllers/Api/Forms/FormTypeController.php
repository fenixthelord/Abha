<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\StoreFormTypeRequest;
use App\Http\Requests\Forms\UpdateFormTypeRequest;
use App\Http\Resources\Forms\FormResource;
use App\Http\Resources\Forms\FormTypeResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\FormType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormTypeController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $permissions = [
            'index'  => ['formtype.show'],
            'store'  => ['formtype.create'],
            'show'  => ['formtype.show'],
            'update'  => ['formtype.update'],
            'destroy'  => ['formtype.delete'],
        ];

        foreach ($permissions as $method => $permissionGroup) {
            foreach ($permissionGroup as $permission) {
                $this->middleware("permission:{$permission}")->only($method);
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $pageNumber = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $formTypes = FormType::with('forms')->paginate($perPage, ['*'], 'page', $pageNumber);
            $data['form_types'] = FormTypeResource::collection($formTypes);

            return $this->PaginateData($data, $formTypes);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $formType = FormType::create($request->validated());
            $data['form'] = FormTypeResource::make($formType);
            DB::commit();
            return $this->returnData($data, "Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
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
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFormTypeRequest $request)
    {
        DB::beginTransaction();
        try {
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
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $formType = FormType::findOrFail($id);
            if (!$formType || $formType->trashed()) {
                return $this->badRequest('Form already deleted.');
            }

            $formType->delete();
            DB::commit();
            return $this->returnSuccessMessage('Form deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);

        }

    }
}
