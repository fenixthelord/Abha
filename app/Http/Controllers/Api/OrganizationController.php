<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\AddOrgRequest;
use App\Http\Requests\Organization\AllRequest;
use App\Http\Requests\Organization\ChartOrgRequest;
use App\Http\Requests\Organization\EditOrgRequest;
use App\Http\Requests\Organization\FilterOrgRequest;
use App\Http\Requests\Organization\ManagerRequest;
use App\Http\Requests\Organization\OrgFilterRequest;
use App\Http\Resources\Chart\HeadChartOrgResource;
use App\Http\Resources\Org\ChartOrgResource;
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

    private const HEAD_MANAGER_POSITION = [
        'en' => "head manager",
        'ar' => "رئيس القسم"
    ];

    public function __construct()
    {
        $permissions = [
            //To be reviewed
            'index'  => ['organization.show'],
            'delete'  => ['organization.delete'],
            'getDepartmentManagers'  => ['organization.show'],
            'getDepartmentEmployees'  => ['organization.show'],
            'AddEmployee'  => ['organization.create'],
            'updateEmployee'  => ['organization.create'],
            'filter'  => ['organization.show','user.show'],
            'chart'  => ['organization.show','user.show'],
        ];

        foreach ($permissions as $method => $permissionGroup) {
            foreach ($permissionGroup as $permission) {
                $this->middleware("permission:{$permission}")->only($method);
            }
        }
    }
    public function getDepartmentManagers(ManagerRequest $request)
    {
        try {
            $department = Department::whereId($request->department_id)->pluck('id')->first();
            $manager = User::whereId($request->manager_id)->pluck('id')->first();
            $employee = Organization::where('department_id', $department)->pluck('employee_id')->toarray();
            if (!in_array($manager, $employee)) {
                $employee[] = $manager;
            }
            $user = User::where('department_id', $department)->whereNotin('id', $employee)->get();
            $data['employees'] = UserResource::collection($user)->each->onlyName();
            return $this->returnData($data);
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    /**
     * old version
     */
    // public function getDepartmentEmployees(AllRequest $request)
    // {

    //     try {
    //         $department = Department::whereId($request->get('department_id'))->pluck("id")->first();
    //         $user = User::where("department_id", $department)->get();
    //         $data['employees'] = UserResource::collection($user)->each->onlyName();
    //         return $this->returnData($data);
    //     } catch (\Exception $exception) {
    //         return $this->handleException($exception);
    //     }
    // }

    /**
     * new version
     */
    public function getDepartmentEmployees(AllRequest $request)
    {
        try {
            $availableManagers = Organization::getManagersAndEmployees($request->department_id);
            $query = User::query();
            if (!empty($availableManagers)) {
                $query->whereIn('id', $availableManagers);
            }

            $mangers = $query->get();
            $data['employees'] = UserResource::collection($mangers)->each->onlyName();

            return $this->returnData($data);
        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    public function AddEmployee(AddOrgRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->user_id == $request->manager_id) {
                return $this->badRequest('manager and employee must not be the same');
            }
            $department = Department::whereId($request->get('department_id'))->pluck('id')->first();
            if (!$department) {
                return $this->badRequest("Department  Not Found");
            }
            $user = User::whereId($request->user_id)->pluck('id')->first();
            if (!$user) {
                return $this->badRequest("User Not Found");
            }
            $us = Organization::where('employee_id', $user)->first();
            if ($us) {
                return $this->badRequest("Employee Already Exists");
            }

            $manager = User::whereId($request->get('manager_id'))->pluck('id')->first();
            if (!$manager) {
                return $this->badRequest("Manager  Not Found");
            }
            $user_dep = User::where('id', $user)->pluck('department_id')->first();
            $manager_dep = User::where('id', $manager)->pluck('department_id')->first();
            if ($user_dep == null || $manager_dep == null) {
                return $this->badRequest("user have no department");
            }
            if ($user_dep != $manager_dep) {
                return $this->badRequest("employee and the manager must be in the same Department");
            }
            $headManager = Organization::where('employee_id', $manager)->first();
            if(!$headManager) {
                Organization::create([
                    'department_id' => $department,
                    'manager_id' => NULL,
                    'employee_id' => $manager,
                    'position' => Self::HEAD_MANAGER_POSITION
                ]);
            }

            $orgUser = Organization::create([
                'department_id' => $department,
                'manager_id' => $manager,
                'employee_id' => $user,
                'position' => $request->position
            ]);
            $data['organization'] = new OrganizationResource($orgUser);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->handleException($exception);
        }
    }

    public function UpdateEmployee(EditOrgRequest $request)
    {
        try {
            if ($request->user_id == $request->manager_id) {
                return $this->badRequest('manager and employee must no be the same');
            }
            $orgUser = Organization::whereId($request->org_id)->first();
            if (!$orgUser) {
                return $this->badRequest("Organization  Not Found");
            }
            $department = Department::whereId($request->get('department_id'))->pluck('id')->first();
            if (!$department) {
                return $this->badRequest("Department  Not Found");
            }

            $user = User::whereId($request->user_id)->pluck('id')->first();


            if (!$user) {
                return $this->badRequest("User Not Found");
            }


            $manager = User::whereId($request->get('manager_id'))->pluck('id')->first();;
            if (!$manager) {
                return $this->badRequest("Manager  Not Found");
            }
            $user_dep = User::where('id', $user)->pluck('department_id')->first();
            $manager_dep = User::where('id', $manager)->pluck('department_id')->first();
            if ($user_dep != $manager_dep) {
                return $this->badRequest("employee and the manager must be in the same Department");
            }
            $orgUser->position = $request->position ? $request->position : $orgUser->poition;
            $orgUser->manager_id = $manager ? $manager : $orgUser->manager_id;
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
            $perPage      = $request->input('per_page', $this->per_page);
            $pageNumber   = $request->input('page', $this->pageNumber);
            $departmentId = $request->input('department_id');
            $managerId    = $request->input('manager_id');

            $query = Organization::query()
                ->when(
                    $departmentId || $managerId,
                    function ($q) use ($departmentId, $managerId) {
                        if ($departmentId) {
                            $q->whereHas("department", function ($q) use ($departmentId) {
                                $q->where("id", $departmentId);
                            });
                        }
                        if ($managerId) {
                            $childManagerIds = Organization::getAllChildIds($managerId);
                            $childManagerIds[] = $managerId;
                            $q->whereIn("manager_id", $childManagerIds);
                        }
                    }
                )
                ->when($request->has("search"), function ($q) use ($request) {
                    $q->withSearch($request->search);
                });

            $organization = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            if ($request->page > $organization->lastPage()) {
                $organization = Organization::paginate($perPage, ['*'], 'page', $pageNumber);
            }

            $data["organizations"] = OrganizationResource::collection($organization);

            return $this->PaginateData($data, $organization);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }



    public function filter(FilterOrgRequest $request)
    {
        try {
            $managersIDs = Organization::getManagersAndEmployees($request->department_id);
            $managers = User::whereIn("id", $managersIDs)->get();
            $data["managers"] = UserResource::collection($managers)->each->onlyName();
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
                'id' => ['required', 'uuid', Rule::exists('organizations', 'id')->where("deleted_at", null)],
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $org = Organization::whereId($request->id)->first();
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
            $managerID = Organization::getOnlyHeadManager($request->department_id);

            $manager = User::findOrFail($managerID);
            $data["chart"] = HeadChartOrgResource::make($manager->load("employees"));
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
