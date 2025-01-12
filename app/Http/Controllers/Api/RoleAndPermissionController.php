<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Permissions\PermissionsResource;
use App\Http\Resources\Roles\Rolesresource;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RoleAndPermissionController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $roles = Role::all();
        return $this->returnData('role',Rolesresource::collection($roles));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }


        Role::create([
            'name' => $request->name,
        ]);
        return $this->returnSuccessMessage('Role created successfully');
    }

    public function assignRole(Request $request, $id)

    {

        try {
            $user = User::FindorFail($id);
            $user->assignRole($request->role);
            return $this->returnSuccessMessage('the role has been assigned successfully');
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }

    }

    public function assignPermission(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'permissions' => 'required|array|min:1', // Ensure at least one permission is passed
            'permissions.*' => 'string|exists:permissions,name',   // Validate each permission exists in the 'permissions' table
        ]);

        try {
            $user = User::findOrFail($id);
            $user->givePermissionTo($request->permissions);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }


    function removeRoleFromUser($userId, Request $request)
    {
        // Find the user by ID
        $validator = Validator::make(['roleName' => $request->roleName], [

            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }
        try {


            $user = User::findOrFail($userId);

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
            'permissions' => 'required|array|min:1',
            'roleName' => 'required|string'
        ]);


        try {
            $role = Role::findByName($request->roleName);
            foreach ($request->permissions as $permission) {
                if ($role->hasPermissionTo($permission)) {
                    $role->revokePermissionTo($permission);

                    return $this->returnSuccessMessage('the role has been removed successfully');
                } else return $this->returnError("The role doesn't have this permission");
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemoveDirectPermission(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array|min:1',
            'permission.*' => 'string|exists:permissions,name',

        ]);
        try {
            $user = User::FindOrFail($user_id);
            foreach ($request->permissions as $permission) {
                // Check if the user has the permission directly (not inherited from roles)
                if ($user->hasDirectPermission($permission)) {
                    // Remove the permission from the user
                    $user->revokePermissionTo($permission);
                    return $this->returnSuccessMessage('the ' . $permission . " permission has been removed successfully");

                } else return $this->returnError(" you can not remove " . $permission . " permission its an role's permission");
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function CreatePermission(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
            'displaying' => 'required|string|unique:permissions,displaying',

            'group' => 'required|string'
        ]);
        if ($validatedData->fails()) {
            return $this->returnError($validatedData->errors());
        }


        // Create a single permission
        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'displaying' => $request->displaying,

            'group' => $request->group,
            'is_admin' => $request->is_admin,
        ]);
 return $this->returnData('permission',Permissionsresource::make($permission));
    }


    public function GetUserPermissions($id)

    {
        try {
            $user = User::FindorFail($id);
            $permission['directed'] = $user->getDirectPermissions();
            $permission['roll'] = $user->getPermissionsViaRoles();
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }

    }

    public function assignPermissionToRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array|min:1',
            'permission.*' => 'string|exists:permissions,name',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }
        try {


            $role = Role::findByName($request->roleName);

            // Assign multiple permissions
            $role->givePermissionTo($request->permissions);
            return $this->returnSuccessMessage('the role has been assigned successfully');
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    function SyncPermission (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array|min:1',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }
        $role=Role::where('name',$request->roleName)->first();
        if(!$role) {
         return $this->returnError("The role doesn't exist");
        }
        $role = $role->syncPermissions($request->permission);
        return $this->returnData('permission',RolesResource::make($role));
    }

    public
    function destroy()
    {

    }
}

