<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\AddOrgRequest;
use App\Http\Requests\Organization\AllRequest;
use App\Http\Requests\Organization\ChartOrgRequest;
use App\Http\Requests\Organization\EditOrgRequest;
use App\Http\Requests\Organization\MangerRequest;
use App\Http\Requests\Organization\OrgFilterRequest;
use App\Http\Resources\Chart\HeadChartOrgResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class OrganizationController extends Controller
{
    use ResponseTrait;

    public function getDepartmentMangers(MangerRequest $request)
    {
        try {

            $department = Department::where('uuid', $request->department_uuid)->pluck('id')->first();
            $manger = User::whereuuid($request->manger_uuid)->pluck('id')->first();
            $employee = Organization::where('department_id', $department)->pluck('employee_id')->toarray();
            if(!in_array($manger, $employee)) {
                $employee[] = $manger;
            }
            $user = User::where('department_id', $department)->whereNotin('id', $employee)->get();
            $data['employees'] = UserResource::collection($user)->each->onlyName();
            return $this->returnData($data);

        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }


    public function getDepartmentEmployees(AllRequest $request)
    {

        try {



            $department = Department::where('uuid', $request->get('department_uuid'))->pluck("id")->first();


            $user = User::where("department_id", $department)->get();
            $data['employees'] = UserResource::collection($user)->each->onlyName();


            return $this->returnData($data);
        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }


    public function AddEmployee(AddOrgRequest $request)
    {
        try {


            if ($request->user_uuid == $request->manger_uuid) {
                return $this->badRequest('manger and employee must not be the same');
            }

            $department = Department::whereuuid($request->get('department_uuid'))->pluck('id')->first();

            if (!$department) {
                return $this->badRequest("Department  Not Found");
            }

            $user = User::whereuuid($request->user_uuid)->pluck('id')->first();


            if (!$user) {
                return $this->badRequest("User Not Found");
            }
            $us = Organization::where('employee_id', $user)->first();
            if ($us) {
                return $this->badRequest("Employee Already Exists");
            }

            $manger = User::whereuuid($request->get('manger_uuid'))->pluck('id')->first();
            if (!$manger) {
                return $this->badRequest("Manger  Not Found");
            }
            $user_dep = User::where('id', $user)->pluck('department_id')->first();
            $manger_dep = User::where('id', $manger)->pluck('department_id')->first();
            if ($user_dep == null || $manger_dep == null) {
                return $this->badRequest("user have no department");
            }
            if ($user_dep != $manger_dep) {
                return $this->badRequest("employee and the manger must be in the same Department");
            }
            $orgUser = Organization::create([

                'department_id' => $department,
                'manger_id' => $manger,
                'employee_id' => $user,
                'position' => $request->position

            ]);


            $data['organization'] = new OrganizationResource($orgUser);
            return $this->returnData($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }


    public function UpdateEmployee(EditOrgRequest $request)
    {

        try {


            if ($request->user_uuid == $request->manger_uuid) {
                return $this->badRequest('manger and employee must no be the same');
            }
            $orgUser = Organization::where('uuid', $request->org_uuid)->first();
            if (!$orgUser) {
                return $this->badRequest("Organization  Not Found");
            }
            $department = Department::whereuuid($request->get('department_uuid'))->pluck('id')->first();
            if (!$department) {
                return $this->badRequest("Department  Not Found");
            }

            $user = User::whereuuid($request->user_uuid)->pluck('id')->first();


            if (!$user) {
                return $this->badRequest("User Not Found");
            }


            $manger = User::whereuuid($request->get('manger_uuid'))->pluck('id')->first();;
            if (!$manger) {
                return $this->badRequest("Manger  Not Found");
            }
            $user_dep = User::where('id', $user)->pluck('department_id')->first();
            $manger_dep = User::where('id', $manger)->pluck('department_id')->first();
            if ($user_dep != $manger_dep) {
                return $this->badRequest("employee and the manger must be in the same Department");
            }
            $orgUser->position = $request->position ? $request->position : $orgUser->poition;
            $orgUser->manger_id = $manger ? $manger : $orgUser->manger_id;
            $orgUser->department_id = $department ? $department : $orgUser->department_id;
            $orgUser->save();
            $data['organization'] = new OrganizationResource($orgUser);
            return $this->returnData($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }

    }


    public function index(OrgFilterRequest $request)
    {
        try {
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);
            $department_uuid = $request->input('department_uuid');
            $manger_uuid = $request->input('manger_uuid');
            $query  = Organization::query()
                ->when(
                    $department_uuid || $manger_uuid,
                    function ($q) use ($request) {
                        if ($request->department_uuid) {
                            $department =  Department::where('uuid', $request->department_uuid)->pluck('id')->first();
                            $q->where("department_id", $department);
                        }
                        if ($request->manger_uuid) {
                            $manger =  User::where('uuid', $request->manger_uuid)->pluck('id')->first();
                            $q->where("manger_id", $manger);
                        }
                    },
                )
                ->when($request->has("search"), function ($q) use ($request) {
                    $q->withSearch($request->search);
                });
            $organization = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            if ($request->page > $organization->lastPage()) {
                $organization = Organization::paginate($perPage, ['*'], 'page', $pageNumber);
            }


            $data["organizations"] = OrganizationResource::collection($organization);

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function filter(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "department_uuid" => [
                    "required",
                    "uuid",
                    Rule::exists('departments', 'uuid')->where("deleted_at", null)
                ]
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $departmentId = Department::where("uuid", $request->department_uuid)->pluck("id")->firstOrFail();
            $mangers = User::query()
                ->MangersInDepartment($departmentId)->get();

            $data["mangers"] = UserResource::collection($mangers)->each->onlyName();
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'uuid' => ['required', 'uuid', Rule::exists('organizations', 'uuid')->where("deleted_at", null)],
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $org = Organization::where('uuid', $request->uuid)->first();
            $org->delete();
            DB::commit();
            return $this->returnSuccessMessage('Organization deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function chart(ChartOrgRequest $request)
    {
        try {
            $mangersIDs = Organization::query()->onlyHeadMangers(
                Department::where("uuid", $request->department_uuid)->pluck("id")->firstOrFail()
            );

            // dd($mangersIDs);
            $mangers = User::whereIn("id", $mangersIDs)->get();
            // dd($mangers);
            $data["chart"] = HeadChartOrgResource::collection($mangers->load("employees"));

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
