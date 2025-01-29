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


class DepartmentsControllers extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            if ($request->search) {
                return $this->search(request());
            }
            $department = Department::paginate($perPage, ['*'], 'page', $pageNumber);
            if ($pageNumber > $department->lastPage() || $pageNumber < 1 || $perPage < 1) {
                return $this->badRequest('Invalid page number');
            }
            $data = [
                'department' => DepartmentResource::collection($department),
                'current_page' => $department->currentPage(),
                'next_page' => $department->nextPageUrl(),
                'previous_page' => $department->previousPageUrl(),
                'total_pages' => $department->lastPage(),
            ];
            return $this->returnData('data', $data, 'success');
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function search(Request $request)
    {
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            $search = $request->search;
            if ($department = Department::where('name','like' , "%$search%")
                ->paginate($perPage, ['*'], 'page', $pageNumber)) {
                if ($pageNumber > $department->lastPage() || $pageNumber < 1 || $perPage < 1) {
                    return $this->badRequest('Invalid page number');
                }

                $data = [
                    'department' => DepartmentResource::collection($department),
                    'current_page' => $department->currentPage(),
                    'next_page' => $department->nextPageUrl(),
                    'previous_page' => $department->previousPageUrl(),
                    'total_pages' => $department->lastPage(),
                ];
                return $this->returnData('data', $data, 'success');
            } else {
                return $this->badRequest('Invalid search');
            }
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function show($uuid)
    {
        try {
            if ($department = Department::whereuuid($uuid)->first()) {
                return $this->returnData('department', DepartmentResource::make($department));
            } else {
                return $this->badRequest('Department not found');
            }
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required','max:255',Rule::unique('departments', 'name->en')]
            ], [
                'name.required' => 'Department name is required.',
                'name.unique' => 'Department name already exists.',
                'name.max' => 'Maximum 255 characters allowed.',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($department = Department::create(['name' => $request->name])) {
                DB::commit();
                return $this->returnData('department', DepartmentResource::make($department), 'success created department');
            } else {
                return $this->badRequest('try again later');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {
            if ($department = Department::whereuuid($uuid)->first()) {
                $validator = Validator::make($request->all(), [
                    'name' => ['nullable', Rule::unique('departments', 'name')->ignore($department->id), 'max:255'],
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError($validator);
                }
                $department->name = $request->name ?? $department->name;
                $department->save();
                DB::commit();
                return $this->returnData('department', DepartmentResource::make($department));
            } else {
                return $this->badRequest('Department not found');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
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
                    $department->name = $department->name.'-'.$department->uuid.'-deleted';
                    $department->save();
                    $department->delete();
                    DB::commit();
                    return $this->returnSuccessMessage('Department deleted successfully');
                }
                else{
                    return $this->badRequest('Department not found');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }
}
