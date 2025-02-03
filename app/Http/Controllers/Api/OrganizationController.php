<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\AddOrgRequest;
use App\Http\Requests\Organization\EditOrgRequest;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {


    }

    public function getDepartmentMangers(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'department_uuids' => ['required', Rule::exists('users', 'uuid')->where("deleted_at", null)],
                'manger_uuid' => ['required', Rule::exists('users', 'uuid')->where("deleted_at", null)]

            ]);
            if ($validation->fails()) {
                $this->returnValidationError($validation);
            }
            $department = Department::where('uuid', $request->department_uuid)->pluck('id')->first();
            $employee = Organization::where('department_id', $department)->pluck('employee_id')->toarray();
            $user = User::where('department_id', $department)->whereNotin('id',$employee)->get();
            $data['employees'] = UserResource::collection($user);
            return $this->returnData($data);

        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }


    public function getDepartmentEmployees(Request $request)
    {

        try {
            $validation = Validator::make($request->all(), [
                'department_uuid' => ['required', Rule::exists('users', 'uuid')->where("deleted_at", null)],


            ]);
            if ($validation->fails()) {
                $this->returnValidationError($validation);
            }
            $department = Department::where('uuid', $request->get('department_uuid'))->pluck("id")->first();


            $user = User::where("department_id", $department)->get();
            $data['employees'] = UserResource::collection($user);


            return $this->returnData($data);
        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }


    }


    public function AddEmployee(AddOrgRequest $request)
    {
        try {


           /* $validation = Validator::make($request->all(), [
                'department_uuids' => 'boolean',
                'manager_uuid' => ['required', Rule::exists('users', 'uuid')->where('deleted_at', null)],
                'user_uuid' => [ 'required', Rule::exists('users', 'uuid')->where("deleted_at", null)],
                'position.en' => 'required|string',
                'position.ar' => 'required|string',
                'position' => 'required|array',
            ]);*/

         /*   $validation = $request->validate([


            ]);


            if ($validation->fails()) {
                $this->returnValidationError($validation);
            }*/
            if ($request->user_uuid == $request->manager_uuid) {
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
     $us=Organization::where('employee_id', $user)->first();
            if($us){
                return $this->badRequest("Employee Already Exists");
            }

            $manger = User::whereuuid($request->get('manager_uuid'))->pluck('id')->first();;
            if (!$manger) {
                return $this->badRequest("Manger  Not Found");
            }
            $user_dep = User::where('id', $user)->pluck('department_id')->first();
            $manger_dep = User::where('id', $manger)->pluck('department_id')->first();
            if($user_dep == null ||  $manger_dep == null){
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




        if ($request->user_uuid == $request->manager_uuid) {
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

        $user =User::whereuuid($request->user_uuid)->pluck('id')->first();


        if (!$user) {
            return $this->badRequest("User Not Found");
        }


        $manger = User::whereuuid($request->get('manager_uuid'))->pluck('id')->first();;
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

    }


}
