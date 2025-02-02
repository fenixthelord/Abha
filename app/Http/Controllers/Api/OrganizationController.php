<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;

class OrganizationController extends Controller
{
    use ResponseTrait;

    public function index(request $request)
    {


    }

    public function getDepartmentMangers(request $request)
    {
        $manger=Organization::where("department_id",$request->get('department_id'))->pluck("manger_id")->toArray();
        $user=User::where("id",'in',$manger)->get();
        $data["Manger"]=UserResource::collection($user);
        return $this->returnData($data);
    }


    public function getDepartmentEmployees(request $request){


        $validation= Validator::make($request->all(),[
            'department_uuid' => 'required|exists:departments,uuid',

        ]);
        if($validation->fails()){
            $this->returnValidationError($validation);
        }
        $manger=User::whereuuid($request->manger_uuid)->pluck("id")->first();
        $users=Organization::where('department_id',$request->department_id)-pluck('employee_id')->toArray();
        $user=User::where("id",'in',$users);
        $data["Employees"]=UserResource::collection($user);
        return $this->returnData($data);




    }

    public function GetEmplyees(request $request){
        $validation= Validator::make($request->all(),[
            'department_uuid' => 'required|exists:departments,uuid',
            'manger_uuid' => 'required|exists:mangers,uuid',
        ]);
        if($validation->fails()){
            $this->returnValidationError($validation);
        }
        $department=Department::where('uuid',$request->get('department_uuid'))->pluck('id')->first();
        $manger=User::where('uuid',$request->get('manger_uuid'))->pluck('id')->first();
        $user=Organization::where('department_id',$department)->where('manger_id',)->pluck('id')->get();
    }






}
