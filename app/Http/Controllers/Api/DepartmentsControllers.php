<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Traits\Paginate;


class DepartmentsControllers extends Controller
{
    use ResponseTrait, Paginate;

    public function index(Request $request)
    {
        try {
            /*$pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
                       $departments = Department::query()
                           ->when($request->has('search'), function ($q) use ($request) {
                               $q->where('name', 'like', '%' . $request->search . '%');
                           });
                       $department = $departments->paginate($perPage, ['*'], 'page', $pageNumber);
                       if ($pageNumber > $department->lastPage() || $pageNumber < 1 || $perPage < 1) {
                           $pageNumber = 1;
                           $department = $departments->paginate($perPage, ['*'], 'page', $pageNumber);
                           $data["groups"] = DepartmentResource::collection($department);
                           return $this->PaginateData($data, $department);
                       }*/
            $fields = ['name'];
            $department = $this->allWithSearch(new Department(), $fields, $request);
            $data['department'] = DepartmentResource::collection($department);
            return $this->PaginateData($data, $department);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function show($uuid)
    {
        try {
            if ($department = Department::whereuuid($uuid)->first()) {
                $data['department'] = DepartmentResource::make($department);
                return $this->returnData($data);
            } else {
                return $this->badRequest('Department not found');
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'array'],
                'name.en' => ['required', 'max:255', Rule::unique('departments', 'name->en')],
                'name.ar' => ['required', 'max:255', Rule::unique('departments', 'name->ar')]
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($department = Department::create(['name' => $request->name])) {
                $data['department'] = DepartmentResource::make($department);

                DB::commit();
                return $this->returnData($data, 'success created department');
            } else {
                return $this->badRequest('try again later');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {

            if ($department = Department::whereuuid($uuid)->first()) {
                $validator = Validator::make($request->all(), [
                    'name' => ['nullable', 'array'],
                    'name.en' => ['required_with:name', 'max:255', Rule::unique('departments', 'name->en')->ignore($department->id)],
                    'name.ar' => ['required_with:name', 'max:255', Rule::unique('departments', 'name->ar')->ignore($department->id)]
                ], messageValidation());
                if ($validator->fails()) {
                    return $this->returnValidationError($validator);
                }
                $department->name = $request->name ?? $department->name;
                $department->save();
                $data['department'] = DepartmentResource::make($department);
                DB::commit();
                return $this->returnData($data);
            } else {
                return $this->badRequest('Department not found');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function destroy($uuid)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|exists:departments,uuid',
            ], [
                'uuid.required' => 'Department uuid is required.',
                'uuid.exists' => 'Department uuid not found.',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if (Department::whereuuid($uuid)->onlyTrashed()->first()) {
                return $this->badRequest('Department already deleted.');
            } else {
                if ($department = Department::whereuuid($uuid)->first()) {
                    $department->name = $department->name . '-' . $department->uuid . '-deleted';
                    $department->save();
                    $department->delete();
                    DB::commit();
                    return $this->returnSuccessMessage('Department deleted successfully');
                } else {
                    return $this->badRequest('Department not found');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
