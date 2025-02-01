<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Permissions\NewPermissionsResource;
use App\Http\Resources\Permissions\PermissionsResource;
use App\Http\Resources\Roles\RolesResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Role\Permission;
use App\Models\Role\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleAndPermissionController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        // Apply middleware to all actions in this controller


    }

    public function index()
    {
if(!auth()->user()->hasPermissionTo("role.show")){
    return $this->Forbidden("you don't have permission to access this page");
}

            $roles = Role::where('name', '!=', 'Master')->get();

        $data['role'] = RolesResource::collection($roles);
        return $this->returnData($data);
    }

    public function store(Request $request)
    {
        \Log::info('Current authenticated user:', [auth()->user()]);
        DB::beginTransaction();
        try {
            if(!auth()->user()->hasPermissionTo("role.create")){
                return $this->Forbidden("you don't have permission to access this page");
            }
            $validator = Validator::make($request->all(), [
                'roleName' => 'required|string|unique:roles,name|regex:/^[^\s]+$/',
                "displayName.en"=> "required|string|unique:roles,displaying",
                 "displayName.ar"=> "required|string|unique:roles,displaying",

                "description.en" => "required|string",
                "description.ar" => "required|string",
                'permission' => 'nullable|array',
                'permission.*' => 'exists:permissions,name'

            ],messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($request->displayName == "Master" || $request->name == "Master") {
                return $this->Forbidden("you are not allowed to create Master role");
            }

            $user = auth()->user();
            if ($user->HasRole('Master')) {
                $request->roleName = "Master_" . $request->roleName;
             $role=Role::where('name',$request->roleName);
             if ($role->exists()) {
                    return $this->badRequest("this role name alredy in use");

                }
            }

            $role = Role::create([
                'name' => $request->roleName,
                "displaying" => $request->displayName,
                'description' => $request->description,
            ]);


            if ($request->has('permission') && !empty($request->permission)) {
                foreach($request->permission as $perm){
                 $permission = Permission::where("name",$perm)->first();
                    if($permission->is_admin == 1){


                        return  $this->Forbidden("this is a Master permission you can not assign it to this role");
                    }
                }
                $role->syncPermissions($request->permission);
            }

            DB::commit();
            return $this->returnSuccessMessage('Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function AssignPermissionsToRole(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'permission' => 'nullable|array',
            'permission.*' => 'exists:permissions,name',
            'roleName' => 'required|string'
        ],messageValidation());
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {

            if ($this->isMasterRole($request->roleName)) {
                return $this->Forbidden("you are not allowed to assign permissions to this role");
            }
            $role = \App\Models\Role\Role::findByName($request->roleName);
            if (!$role) {
                return $this->NotFound('Role not found');
            }

            // Assign multiple permissions


            foreach ($request->permission as $permission) {
                $permission = Permission::FindByName($permission);
                if (!$permission) {
                    return $this->NotFound('Permission not found');
                }

                if ($permission->is_admin == true) {
                    if ($role->name != 'Master') {
                        return $this->Forbidden('this is master role permission you can not assign it to this role');
                    }
                }


                $role->givePermissionTo($request->permission);

                $data['role'] = RolesResource::make($role);
                return $this->returnData($data);
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }



    public function assignRole(Request $request)

    {
        if (!auth()->user()->hasPermissionTo("user.update")) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',

            'user_uuid' => 'required|exists:users,uuid',
        ],messageValidation());
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            $user = User::where("uuid", $request->user_uuid)->first();
            if (!$user) {
                return $this->NotFound('User not found');
            }
            if ($request->role == 'Master') {
                return $this->Forbidden('this is master role you can not assign it to this user');
            }
            $user->syncRoles($request->role);

            return $this->returnSuccessMessage('the role has been assigned successfully');
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function assignPermission(Request $request)
    {
        if (!auth()->user()->hasPermissionTo("user.update")) {
            return $this->Forbidden("You are not authorized to do this action");
        }
        $validatedData = Validator::make($request->all(), [
            'user_uuid' => 'required|exists:users,uuid',
            'permissions' => 'nullable'

        ],messageValidation());
        if ($validatedData->fails()) {
            return $this->returnValidationError($validatedData);
        }
        try {
            $user = User::where("uuid", $request->user_uuid)->first();
            if (!$user) {
                return $this->NotFound('User not found');
            }

            foreach ($request->permissions as $permission) {

                $single_permission = Permission::where('name', $permission)->first();
                if (!$single_permission) {
                    return $this->returnError('this permission does not exist');
                }

                if ($single_permission->is_admin == true) {

                    if (!$user->hasRole('Master')) {
                        //                                $user->givePermissionTo($permission);
                        return $this->Forbidden('this is master permission you can not assign it to this user');


                    }
                }

                $user->givePermissionTo($request->permissions);


                return $this->returnSuccessMessage('the permission has been assigned successfully');
            }


        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    function removeRoleFromUser(Request $request)
    {
        // Find the user by ID
        if (!auth()->user()->hasPermissionTo("user.update")) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        $validator = Validator::make($request->all(), [
            'user_uuid' => 'required|exists:users,uuid',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {


            $user = User::where("uuid", $request->user_uuid)->first();
            if (!$user) {
                return $this->NotFound('User not found');
            }
            if ($user->hasRole('Master')) {
                if ($request->roleName == 'Master') {
                    return $this->Forbidden('this is master role  you can not remove it from Master');
                }
            }
            if (!$user->roles()->where('name', $request->roleآame)->exists()) {
                return $this->Forbidden('this role does not exist');
            }
            // Check if the user has the role before removing it
            if ($user->hasRole($request->roleName)) {
                // Remove the role from the user
                $user->removeRole($request->roleName);

                return $this->returnSuccessMessage('the role has been removed successfully');
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemovePermissionsFromRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
            'roleName' => 'required|string|exists:roles,name',
        ],messageValidation());

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            DB::beginTransaction();
            if ($this->isMasterRole($request->roleName)) {
                return $this->Forbidden("you are not allowed to remove permissions to this role");
            }
            // Find the role
            $role = Role::findByName($request->roleName);

            // Filter permissions to those the role actually has
            $permissions = collect($request->permissions)
                ->filter(fn($permission) => $role->hasPermissionTo($permission));

            // Revoke the filtered permissions
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }

            $data['role'] = RolesResource::make($role);

            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemoveDirectPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array|min:1',
            'permission.*' => 'string|exists:permissions,name',
            'user_uuid' => 'required|exists:users,uuid',
        ],messageValidation());

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            DB::beginTransaction();

            // Fetch the user
            $user = User::where("uuid", $request->user_uuid)->first();

            if (!$user) {
                DB::rollBack();
                return $this->NotFound('User not found');
            }

            $permissions = $request->permission;
            $removedPermissions = [];
            $failedPermissions = [];

            foreach ($permissions as $permission) {
                // Check if the user has the permission directly
                if ($user->hasDirectPermission($permission)) {
                    $user->revokePermissionTo($permission);
                    $removedPermissions[] = $permission;
                } else {
                    $failedPermissions[] = $permission;
                }
            }
            $data['result'] = [
                'removed_permissions' => $removedPermissions,
                'failed_permissions' => $failedPermissions,
            ];

            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function CreatePermission(Request $request)
    {


        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name|regex:/^[^\s]+$/',
            'displaying' => 'required|string|unique:permissions,displaying',

            'group' => 'required|string'
        ]);
        if ($validatedData->fails()) {
            return $this->returnValidationError($validatedData);
        }

        try {

            // Create a single permission
            DB::beginTransaction();

            $permission = Permission::create([
                'name' => $request->name,

                'displaying' => $request->displaying,

                'group' => $request->group,
                'is_admin' => $request->is_admin,
            ]);
            $data['permission'] =  Permissionsresource::make($permission);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(400, $exception->getMessage());
        }
    }

    public function GetUserPermissions(Request $request)

    {
        try {

            $user = User::where("uuid", $request->user_uuid)->first();
            if (!$user) {
                return $this->NotFound('User not found');
            } else {
                $permission['directed'] = $user->getDirectPermissions();
                $permission['roll'] = $user->getPermissionsViaRoles();
                $data['permission'] =  [
                    'directed' => PermissionsResource::collection($permission['directed']),
                    'roll' => PermissionsResource::collection($permission['roll'])
                ];
                return $this->returnData($data);
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function SyncPermission(Request $request)
    {
        if (!auth()->user()->hasPermissionTo("role.update")) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,name',
            'roleName' => 'required|string|exists:roles,name',
            'displayName' => 'string',
            'description' => 'string',
        ],messageValidation());

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            // Prevent Master Role Updates
            if ($request->roleName === "Master") {
                return $this->returnError('You cannot update the Master role ');
            }

            DB::beginTransaction();

            // Retrieve the role
            if ($this->isMasterRole($request->roleName)) {
                if (!auth()->user()->hasRole("Master")) {
                    return $this->Forbidden("you are not allowed to update this role");
                }
            }
            $role = Role::findByName($request->roleName);

            if (!$role) {
                DB::rollBack();
                return $this->NotFound('Role not found');
            }

            $role->displaying = $request->displayName ? $request->displayName : $role->displaying;
            $role->description = $request->description ? $request->description : $role->description;
            $role->save();

            // Sync permissions
            foreach ($request->permission as $permissionName) {
                $permission = Permission::findByName($permissionName);
                if (!$permission) {
                    return $this->NotFound('Permission not found');
                }
                if ($permission->is_admin==true) {
                    return $this->Forbidden("you are not allowed to add Master permission");
                }
            }
            $role->syncPermissions($request->permission);
            $data['role'] = RolesResource::make($role);
            DB::commit();

            return $this->returnData($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function GetAllPermissions()
    {
        try {
            if (!auth()->user()->hasPermissionTo("permission.show")) {
                return $this->Forbidden("you don't have permission to access this page");
            }

                $permission = Permission::where('is_admin', false)->get();


            $resource = new NewPermissionsResource($permission);
            $data['permission'] = $resource;
            return $this->returnData($data);
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function DeleteRole(Request $request)
    {
        if (!auth()->user()->hasPermissionTo("role.delete")) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        $validator = Validator::make($request->all(), [
            'roleName' => 'required|array',
            'roleName.*' => 'exists:roles,name',
        ],messageValidation());
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {

            DB::beginTransaction();
            $user = auth()->user();
            foreach ($request->roleName as $role) {
                $roles = Role::findByName($role);

                if (!$roles) {
                    return $this->NotFound('Role not found');
                }
                if ($this->isMasterRole($role)) {

                    /*  if(!$user->hasRole("Master")) {
                          return $this->forbidden('You cannot delete the Master role');

                      }*/
                    return $this->forbidden('You cannot delete the Master role');
                }
                if ($role == "Master") {
                    return $this->Forbidden('you cannot delete  Master');
                }
                $roles->delete();
            }
            DB::commit();
            return $this->returnSuccessMessage('the role deleted successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }
    public function ShowRole(Request $request){
        $validator = Validator::make($request->all(), [
            'roleName' => 'required|exists:roles,name'
        ],
           messageValidation() );
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {


            $roles = Role::findByName($request->roleName);

        if (!$roles) {
            return $this->NotFound('Role not found');
        }
       $data['role']=RolesResource::make($roles);

        return $this->returnData($data);
    }
    catch (\Exception $exception){
        return  $this->handleException($exception);}
    }
    public function isMasterRole($roleName)
    {
        return str_starts_with($roleName, 'Master_');
    }
}
