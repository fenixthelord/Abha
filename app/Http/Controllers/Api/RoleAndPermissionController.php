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
use App\Http\Traits\Paginate;

class RoleAndPermissionController extends Controller
{
    use ResponseTrait, Paginate;

    public $translatable = ["displaying", "description"];

    public function __construct()
    {
        $permissions = [
            'index'  => ['role.show'],
            'ShowRole'  => ['role.show'],
            'store' => ['role.create'],
            'SyncPermission'    => ['role.update'],
            'assignPermission'    => ['role.update'],
            'assignRole'    => ['role.update'],
            'DeleteRole'   => ['role.delete'],
            'removeRoleFromUser'   => ['user.update'],
            'GetAllPermissions'   => ['permission.update'],
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware('permission:' . implode('|', $permission))->only($method);
        }
    }

    public function index(Request $request)
    {
//        if (!auth()->user()->hasPermissionTo("role.show")) {
//            return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//        }
        /*$roles = Role::where('name', '!=', 'Master')->get();
        $data['role'] = RolesResource::collection($roles)->each->withTranslate();
        return $this->returnData($data);*/
        $fields = ['displaying->ar', 'displaying->en'];
        $roles = $this->allWithSearch(new Role(), $fields, $request, 'name', 'Master', '!=');
        $date['roles'] = RolesResource::collection($roles);
        return $this->PaginateData($date, $roles);
    }

    public function store(Request $request)
    {
        \Log::info('Current authenticated user:', [auth()->user()]);
        DB::beginTransaction();
        try {
//            if (!auth()->user()->hasPermissionTo("role.create")) {
//                return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//            }
            $validator = Validator::make($request->all(), [
                // 'roleName' => 'required|string|unique:roles,name|regex:/^[^\s]+$/',
                "displaying.en" => "required|string|unique:roles,displaying",
                "displaying.ar" => "required|string|unique:roles,displaying",

                "description.en" => "required|string",
                "description.ar" => "required|string",
                'permission' => 'nullable|array',
                'permission.*' => 'exists:permissions,name'

            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($request->displaying == "Master" || $request->name == "Master") {
                return $this->Forbidden(__('validation.custom.roleAndPerm.not_allowed_create_Master_role'));
            }

            $words = explode(' ', $request->displaying['en']);

            $name = implode(".", $words);



            $user = auth()->user();
            if ($user->HasRole('Master')) {
                $request->roleName = "Master_" . $name;
                $role = Role::where('name', $name)->first();
            }
            if (Role::where('name', $name)->exists()) {
                return $this->badRequest(__('validation.custom.roleAndPerm.role_name_already_in_use'));
            }

            $role = new Role([
                'name' => $name,

            ]);
            foreach ($this->translatable as $field) {
                $role->setTranslation($field, 'en', $request->input("$field.en"));
                $role->setTranslation($field, 'ar', $request->input("$field.ar"));
            }
            $role->save();

            if ($request->has('permission') && !empty($request->permission)) {
                foreach ($request->permission as $perm) {
                    $permission = Permission::where("name", $perm)->first();
                    if ($permission->is_admin == 1) {
                        return $this->Forbidden(__('validation.custom.roleAndPerm.master_permission'));
                    }
                }
                $role->syncPermissions($request->permission);
            }

            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.roleAndPerm.role_created_successfully'));
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
        ], messageValidation());
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            if ($this->isMasterRole($request->roleName)) {
                return $this->Forbidden(__('validation.custom.roleAndPerm.not_allowed_assign_permissions_to_role'));
            }
            $role = \App\Models\Role\Role::findByName($request->roleName);
            if (!$role) {
                return $this->NotFound(__('validation.custom.roleAndPerm.role_not_found'));
            }

            // Assign multiple permissions
            foreach ($request->permission as $permission) {
                $permission = Permission::FindByName($permission);
                if (!$permission) {
                    return $this->NotFound(__('validation.custom.roleAndPerm.permission_not_found'));
                }
                if ($permission->is_admin == true) {
                    if ($role->name != 'Master') {
                        return $this->Forbidden(__('validation.custom.roleAndPerm.master_role_permission'));
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
//        if (!auth()->user()->hasPermissionTo("user.update")) {
//            return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//        }
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',

            'user_id' => 'required|exists:users,id',
        ], messageValidation());
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            $user = User::whereId($request->user_id)->first();
            if (!$user) {
                return $this->NotFound(__('validation.custom.roleAndPerm.user_not_found'));
            }
            if ($request->role == 'Master') {
                return $this->Forbidden(__('validation.custom.roleAndPerm.master_role_can_not_assign_user'));
            }
            $user->syncRoles($request->role);

            return $this->returnSuccessMessage(__('validation.custom.roleAndPerm.role_has_been_assigned_successfully'));
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function assignPermission(Request $request)
    {
//        if (!auth()->user()->hasPermissionTo("user.update")) {
//            return $this->Forbidden(__('validation.custom.roleAndPerm.forbidden_action'));
//        }
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'permissions' => 'nullable'

        ], messageValidation());
        if ($validatedData->fails()) {
            return $this->returnValidationError($validatedData);
        }
        try {
            $user = User::whereId($request->user_id)->first();
            if (!$user) {
                return $this->NotFound(__('validation.custom.roleAndPerm.user_not_found'));
            }

            foreach ($request->permissions as $permission) {
                $single_permission = Permission::where('name', $permission)->first();
                if (!$single_permission) {
                    return $this->returnError(__('validation.custom.roleAndPerm.permission_not_exist'));
                }

                if ($single_permission->is_admin == true) {
                    if (!$user->hasRole('Master')) {
                        //$user->givePermissionTo($permission);
                        return $this->Forbidden(__('validation.custom.roleAndPerm.master_permission_user'));
                    }
                }
                $user->givePermissionTo($request->permissions);
                return $this->returnSuccessMessage(__('validation.custom.roleAndPerm.permission_assigned_successfully'));
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    function removeRoleFromUser(Request $request)
    {
        // Find the user by ID
//        if (!auth()->user()->hasPermissionTo("user.update")) {
//            return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//        }
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            $user = User::whereId($request->user_id)->first();
            if (!$user) {
                return $this->NotFound(__('validation.custom.roleAndPerm.user_not_found'));
            }
            if ($user->hasRole('Master')) {
                if ($request->roleName == 'Master') {
                    return $this->Forbidden(__('validation.custom.roleAndPerm.master_role_cannot_remove_from_Master'));
                }
            }
            if (!$user->roles()->where('name', $request->roleName)->exists()) {
                return $this->Forbidden(__('validation.custom.roleAndPerm.role_not_exist'));
            }
            // Check if the user has the role before removing it
            if ($user->hasRole($request->roleName)) {
                // Remove the role from the user
                $user->removeRole($request->roleName);

                return $this->returnSuccessMessage(__('validation.custom.roleAndPerm.role_removed_successfully'));
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
        ], messageValidation());

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            DB::beginTransaction();
            if ($this->isMasterRole($request->roleName)) {
                return $this->Forbidden(__('validation.custom.roleAndPerm.not_allowed_remove_permissions_to_this_role'));
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
            'user_id' => 'required|exists:users,id',
        ], messageValidation());

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            DB::beginTransaction();

            // Fetch the user
            $user = User::whereId($request->user_id)->first();

            if (!$user) {
                DB::rollBack();
                return $this->NotFound(__('validation.custom.roleAndPerm.user_not_found'));
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
            $data['permission'] = Permissionsresource::make($permission);
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
            $user = User::whereId($request->user_id)->first();
            if (!$user) {
                return $this->NotFound(__('validation.custom.roleAndPerm.user_not_found'));
            } else {
                $permission['directed'] = $user->getDirectPermissions();
                $permission['roll'] = $user->getPermissionsViaRoles();
                $data['permission'] = [
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
//        if (!auth()->user()->hasPermissionTo("role.update")) {
//            return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//        }
        $validator = Validator::make($request->all(), [
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,name',
            'roleName' => 'required|string|exists:roles,name',
            'displaying.en' => 'required|string',
            'displaying.ar' => 'required|string',
            'description.ar' => 'required|string',
            'description.en' => 'required|string',
        ], messageValidation());

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            // Prevent Master Role Updates
            if ($request->roleName === "Master") {
                return $this->returnError(__('validation.custom.roleAndPerm.cannot_update_Master_role'));
            }
            DB::beginTransaction();
            // Retrieve the role
            if ($this->isMasterRole($request->roleName)) {
                if (!auth()->user()->hasRole("Master")) {
                    return $this->Forbidden(__('validation.custom.roleAndPerm.not_allowed_update_role'));
                }
            }
            $role = Role::findByName($request->roleName);

            if (!$role) {
                DB::rollBack();
                return $this->NotFound(__('validation.custom.roleAndPerm.role_not_found'));
            }

            foreach ($this->translatable as $field) {
                $role->setTranslation($field, 'en', $request->input("$field.en"));
                $role->setTranslation($field, 'ar', $request->input("$field.ar"));
            }
            $role->save();
            // Sync permissions
            foreach ($request->permission as $permissionName) {
                $permission = Permission::findByName($permissionName);
                if (!$permission) {
                    return $this->NotFound(__('validation.custom.roleAndPerm.permission_not_found'));
                }
                if ($permission->is_admin == true) {
                    return $this->Forbidden(__('validation.custom.roleAndPerm.not_allowed_add_Master_permission'));
                }
            }
            $role->syncPermissions($request->permission);
            $data['role'] = RolesResource::make($role)->withTranslate();
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
//            if (!auth()->user()->hasPermissionTo("permission.show")) {
//                return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//            }

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
//        if (!auth()->user()->hasPermissionTo("role.delete")) {
//            return $this->Forbidden(__('validation.custom.roleAndPerm.dont_have_permission'));
//        }
        $validator = Validator::make($request->all(), [
            'roleName' => 'required|array',
            'roleName.*' => 'exists:roles,name',
        ], messageValidation());
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            DB::beginTransaction();
            $user = auth()->user();
            foreach ($request->roleName as $role) {
                $roles = Role::findByName($role);

                if (!$roles) {
                    return $this->NotFound(__('validation.custom.roleAndPerm.role_not_found'));
                }
                if ($this->isMasterRole($role)) {
                    /*  if(!$user->hasRole("Master")) {
                          return $this->forbidden('You cannot delete the Master role');
                      }*/
                    return $this->forbidden(__('validation.custom.roleAndPerm.master_role_cannot_be_deleted'));
                }
                if ($role == "Master") {
                    return $this->Forbidden(__('validation.custom.roleAndPerm.master_cannot_be_deleted'));
                }
                $roles->delete();
            }
            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.roleAndPerm.role_deleted_successfully'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function ShowRole(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'roleName' => 'required|exists:roles,name'
            ]
        );
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            $roles = Role::findByName($request->roleName);
            if (!$roles) {
                return $this->NotFound(__('validation.custom.roleAndPerm.role_not_found'));
            }

            $data['role'] = RolesResource::make($roles)->withTranslate();

            return $this->returnData($data);
        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    public function isMasterRole($roleName)
    {
        return str_starts_with($roleName, 'Master_');
    }
}
