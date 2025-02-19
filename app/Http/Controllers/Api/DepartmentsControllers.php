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

    public function __construct()
    {
        $permissions = [
            'index'  => ['department.show'],
            'show'  => ['department.show'],
            'store' => ['department.create'],
            'update'    => ['department.update'],
            'destroy'   => ['department.delete'],
        ];

        foreach ($permissions as $method => $permissionGroup) {
            foreach ($permissionGroup as $permission) {
                $this->middleware("permission:{$permission}")->only($method);
            }
        }
    }
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
            $fields = ['name->ar', 'name->en'];
            $department = $this->allWithSearch(new Department(), $fields, $request);
            $data['department'] = DepartmentResource::collection($department);
            return $this->PaginateData($data, $department);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function show(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'exists:departments,id']
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $id = $request->input('id');
            if ($department = Department::find($id)) {
                $data['department'] = DepartmentResource::make($department);
                return $this->returnData($data);
            } else {
                return $this->badRequest(__('validation.custom.department.notfound'));
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
                'name' => ['required', 'array', 'max:254'],
                'name.en' => ['required', 'max:255', Rule::unique('departments', 'name->en')],
                'name.ar' => ['required', 'max:255', Rule::unique('departments', 'name->ar')]
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($department = Department::create(['name' => $request->name])) {
                $data['department'] = DepartmentResource::make($department);

                DB::commit();
                return $this->returnData($data, __('validation.custom.department.done'));
            } else {
                return $this->badRequest(__('validation.custom.department.try'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'exists:departments,id']
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $id = $request->input('id');
            if ($department = Department::find($id)) {
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
                return $this->badRequest(__('validation.custom.department.notfound'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:departments,id',
            ], [
                'id.required' => 'Department id is required.',
                'id.exists' => 'Department id not found.',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $id = $request->input('id');
            if (Department::onlyTrashed()->find($id)) {
                return $this->badRequest(__('validation.custom.department.deleted'));
            } else {
                if ($department = Department::find($id)) {
                    $name = $department->getTranslations("name");
                    $department->name = [
                        'en' => $name['en'] . '-' . $department->id . '-deleted',
                        'ar' => $name['ar'] . '-' . $department->id . '-محذوف'
                    ];
                    $department->save();
                    $department->deleteWithChildren();
                    $department->delete();
                    DB::commit();
                    return $this->returnSuccessMessage(__('validation.custom.department.delete'));
                } else {
                    return $this->badRequest(__('validation.custom.department.notfound'));
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
