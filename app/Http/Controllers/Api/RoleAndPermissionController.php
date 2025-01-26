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
        $user = auth()->user();
        if (!$user->hasPermissionTo('role.show')) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        if (auth()->user()->hasRole('Master')) {
            $roles = Role::all();

        } else {
            $roles = Role::where('name', '!=', 'Master')->get();
        }
        return $this->returnData('role', RolesResource::collection($roles));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('role.create')) {
            return $this->Forbidden("You don't have permission to create role");
        }
        \Log::info('Current authenticated user:', [auth()->user()]);
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'roleName' => 'required|string|unique:roles,name|regex:/^[^\s]+$/',
                "displayName" => "required|string|unique:roles,displaying",
                "description" => "required|string",
                'permission' => 'nullable|array',
                'permission.*' => 'exists:permissions,name'

            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            if ($request->displayName == "Master" || $request->name == "Master") {
                return $this->Forbidden("you are not allowed to create Master role");
            }

            if ($user->HasRole('Master')) {
                $request->roleName = "Master_" . $request->roleName;
            }

            $role = Role::create([
                'name' => $request->roleName,
                "displaying" => $request->displayName,
                'description' => $request->description,
            ]);


            if ($request->has('permission') && !empty($request->permission)) {
                foreach ($request->permission as $perm) {
                    $permission = Permission::where("name", $perm)->first();
                    if ($permission->is_admin == 1) {


                        return $this->Forbidden("this is a Master permission you can not assign it to this role");
                    }
                }
                $role->syncPermissions($request->permission);
            }

            DB::commit();
            return $this->returnSuccessMessage('Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    public function AssignPermissionsToRole(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'permission' => 'nullable|array',
            'permission.*' => 'exists:permissions,name',
            'roleName' => 'required|string'
        ]);
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


                return $this->returnData('role', RolesResource::make($role));
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function isMasterRole($roleName)
    {
        return str_starts_with($roleName, 'Master_');
    }

    public function assignRole(Request $request)

    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',

            'user_uuid' => 'required|exists:users,uuid',
        ]);
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
        if (!auth()->user()->hasRole("Master_Admin")) {
            return $this->Forbidden("You are not authorized to do this action");
        }
        $validatedData = Validator::make($request->all(), [
            'user_uuid' => 'required|exists:users,uuid',
            'permissions' => 'nullable'

        ]);
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
        ]);

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

            DB::commit();

            return $this->returnData('role', RolesResource::make($role));
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
        ]);

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

            DB::commit();

            return $this->returnData('result', [
                'removed_permissions' => $removedPermissions,
                'failed_permissions' => $failedPermissions,
            ]);
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
            DB::commit();
            return $this->returnData('permission', Permissionsresource::make($permission));
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
                return $this->returnData('permission', [
                    'directed' => PermissionsResource::collection($permission['directed']),
                    'roll' => PermissionsResource::collection($permission['roll'])
                ]);
            }
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function SyncPermission(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('role.update')) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,name',
            'roleName' => 'required|string|exists:roles,name',
            'displayName' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            // Prevent Master Role Updates
            if ($request->displayName === "Master" || $request->roleName === "Master") {
                return $this->returnError('You cannot update the Master role');
            }

            DB::beginTransaction();


            if ($this->isMasterRole($request->roleName)) {
                return $this->Forbidden("you are not allowed to update this role");
            }
            $role = Role::findByName($request->roleName);

            if (!$role) {
                DB::rollBack();
                return $this->NotFound('Role not found');
            }

            // Update role metadata
            $role->update([
                'displaying' => $request->displayName,
                'description' => $request->description,
            ]);

            // Sync permissions
            $role->syncPermissions($request->permission);

            DB::commit();

            return $this->returnData('role', RolesResource::make($role));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function GetAllPermissions()
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('permission.show')) {
            return $this->Forbidden("you don't have permission to access this page");
        }

        try {
            if (auth()->user()->hasrole('Master')) {
                $permission = Permission::all();
            } else {

                $permission = Permission::where('is_admin', false)->get();
            }

            $resource = new NewPermissionsResource($permission);

            return $this->returnData('permission', $resource);
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }

    }

    public function DeleteRole(Request $request)
    {

        $user = auth()->user();
        if (!$user->hasPermissionTo('role.delete')) {
            return $this->Forbidden("you don't have permission to delete this role");
        }
        $validator = Validator::make($request->all(), [
            'roleName' => 'required|array',
            'roleName.*' => 'exists:roles,name',
        ]);
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
}
