<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Permissions\NewPermissionsResource;
use App\Http\Resources\Permissions\PermissionsResource;
use App\Http\Resources\Roles\RolesResource;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        // Apply middleware to all actions in this controller
         $this->middleware('super-admin')->only(['store']);
    }

    public function index()
    {
        try {


            $roles = Role::all();
            return $this->returnData('role', RolesResource::collection($roles));
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'roleName' => 'required|string|unique:roles,name',
                "description" => "required|string",
                'permission' => 'nullable',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator, 400, $validator->errors());
            }


            $role = Role::create([
                'name' => $request->roleName,
                'description' => $request->description,
            ]);
            $request->roleName = $role->name;

            $this->AssignPermissionsToRole($request);
            DB::commit();
            return $this->returnSuccessMessage('Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return abort(400);
        }

    }

    public function AssignPermissionsToRole(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'permission' => 'required',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator, 400, $validator->errors());
        }
        try {


            $role = Role::findByName($request->roleName);


            // Assign multiple permissions
            $permission = exploder($request->permission);

            $role->givePermissionTo($permission);
            return $this->returnData('role', RolesResource::make($role));
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function assignRole(Request $request)

    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',

            'user_id' => 'required|integer|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator, 400, $validator->errors());
        }

        try {
            $user = User::findOrFail($request->user_id);

            $user->assignRole($request->role);
            return $this->returnSuccessMessage('the role has been assigned successfully');
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }

    }

    public function assignPermission(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'permissions' => 'nullable'

        ]);
        if ($validatedData->fails()) {
            return $this->returnValidationError($validatedData, 400, $validatedData->errors());
        }
        try {
            $user = User::findOrFail($request->user_id);
            $permissions = exploder($request->permissions);
            $user->givePermissionTo($permissions);
            return $this->returnSuccessMessage('the permission has been assigned successfully');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    function removeRoleFromUser(Request $request)
    {
        // Find the user by ID
        $validator = Validator::make(['roleName' => $request->roleName], [

            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator, 400, $validator->errors());
        }
        try {


            $user = User::findOrFail($request->user_id);

            // Check if the user has the role before removing it
            if ($user->hasRole($request->roleName)) {
                // Remove the role from the user
                $user->removeRole($request->roleName);

                return $this->returnSuccessMessage('the role has been removed successfully');
            } else {
                return $this->returnError("The user doesn't have this role");
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemovePermissionsFromRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator, 400, $validator->errors());
        }


        try {
            $role = Role::findByName($request->roleName);


            if (is_array($request->permissions)) {
                foreach ($request->permissions as $permission) {
                    if ($role->hasPermissionTo($permission)) {
                        $role->revokePermissionTo($permission);
                        return $this->returnData('role', RolesResource::make($role));
                    } else return $this->returnError("The role doesn't have this permission");
                }
            } else {
                if ($role->hasPermissionTo($request->permissions)) {
                    $role->revokePermissionTo($request->permissions);
                    return $this->returnData('role', RolesResource::make($role));
                } else return $this->returnError("The role doesn't have this permission");
            }


        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemoveDirectPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required',
            'user_id' => 'required|integer|exists:users,id'


        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator, 400, $validator->errors());
        }
        try {


            $user = User::FindOrFail($request->user_id);

//            if (is_array($request->permission)) {


            foreach ($request->permission as $permission) {
                // Check if the user has the permission directly (not inherited from roles)


                if ($user->hasDirectPermission($permission)) {
                    // Remove the permission from the user
                    $user->revokePermissionTo($permission);
                } else return $this->returnError(" you can not remove " . $permission . " permission its an role's permission");
            }
            return $this->returnSuccessMessage('the permission removed successfully');

//            }
//        else {
//                if ($user->hasDirectPermission($request->permission)) {
//                    // Remove the permission from the user
//                    $user->revokePermissionTo($request->permission);
//                    return $this->returnSuccessMessage('the ' . $request->permission . " permission has been removed successfully");
//
//                } else return $this->returnError(" you can not remove " . $request->permission . " permission its an role's permission");
//            }


        } catch (\Exception $exception) {
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
            return $this->returnValidationError($validatedData, 400, $validatedData->errors());
        }


        // Create a single permission
        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'displaying' => $request->displaying,

            'group' => $request->group,
            'is_admin' => $request->is_admin,
        ]);
        return $this->returnData('permission', Permissionsresource::make($permission));
    }

    public function GetUserPermissions(Request $request)

    {
        try {
            $user = User::FindorFail($request->user_id);

            $permission['directed'] = $user->getDirectPermissions();
            $permission['roll'] = $user->getPermissionsViaRoles();
            return $this->returnData('permission', [
                'directed' => PermissionsResource::collection($permission['directed']),
                'roll' => PermissionsResource::collection($permission['roll'])
            ]);
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }

    }

    function SyncPermission(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'permission' => 'required|array|min:1',
                'roleName' => 'required|string|exists:roles,name',
                'newName' => 'required|string|unique:roles,name',
                'description' => 'required|string'
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator, 400, $validator->errors());
            }


            $role = Role::FindByName($request->roleName);


            $role->update(['name' => $request->newName, 'description' => $request->decription]);


            $role = $role->syncPermissions($request->permission);
            Db::commit();
            return $this->returnData('permission', RolesResource::make($role));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function GetAllPermissions()
    {
        try {
            $permission = Permission::all();
            return $this->returnData('permission',NewPermissionsResource::collection($permission));
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }

    }

    public function DeleteRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roleName' => 'required|string|exists:roles,name',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator, 400, $validator->errors());
        }
        try {
            $role = Role::findByName($request->roleName);
            $role->delete();
            return $this->returnSuccessMessage('the role deleted successfully');
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }


}

