<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Permissions\NewPermissionsResource;
use App\Http\Resources\Permissions\PermissionsResource;
use App\Http\Resources\Roles\RolesResource;
use App\Models\User;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Role\Permission;
use App\Models\Role\Role;
use function PHPUnit\Framework\lessThanOrEqual;

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

        if(auth()->user()->hasRole('Master')){
            $roles = Role::all();

        }
        else {
            $roles = Role::where('name','!=','Master')->get();
        }
        return $this->returnData('role', RolesResource::collection($roles));
    }

    public function store(Request $request)
    {
        \Log::info('Current authenticated user:', [auth()->user()]);
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'roleName'=>'required|string|unique:roles,name',
                "description" => "required|string",
                'permission' => 'nullable',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $role = Role::create([
                'name' => $request->roleName,
                'description' => $request->description,

            ]);
            $request->roleName = $role->name;


            $role->syncPermissions($request);
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
            return $this->returnValidationError($validator);
        }
        try {


            $role = \App\Models\Role\Role::findByName($request->roleName);
            if(!$role){
                return $this->NotFound('Role not found');
            }

            // Assign multiple permissions
       else{
            $permissions = exploder($request->permission);
foreach ($permissions as $permission) {
    $permission=Permission::FindByName($permission);
        if(!$permission){
            return $this->NotFound('Permission not found');
        }
        else{
    if($permission->is_admin==true){
        if($role->name =='Master'){
            $role->givePermissionTo($permission);
        }
        else{
            return $this->returnError('this is master role permission you can not assign it to this role');
        }}
        else{
            $role->givePermissionTo($permission);
        }
    }


            return $this->returnData('role', RolesResource::make($role));
        }}
        }catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
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
            $user = User::where("uuid",$request->user_uuid)->first();
            if(!$user){
                return $this->NotFound('User not found');
            }
            else{
if($request->role == 'Master'){
    return $this->Forbidden('this is master role you can not assign it to this user');
}
        else  {  $user->syncRoles($request->role);}
            return $this->returnSuccessMessage('the role has been assigned successfully');}
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function assignPermission(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'user_uuid' => 'required|exists:users,uuid',
            'permissions' => 'nullable'

        ]);
        if ($validatedData->fails()) {
            return $this->returnValidationError($validatedData);
        }
        try {
            $user = User::where("uuid",$request->user_uuid)->first();
if(!$user){
    return $this->NotFound('User not found');
}
else{
            //  $permissions = explode(",",$request->permissions);

            foreach ($request->permissions as $permission) {

                $single_permission = Permission::where('name', $permission)->first();
                if ($single_permission) {
                    if ($single_permission->is_admin == true) {


                        if ($user->hasRole('Master')) {
                            $user->givePermissionTo($permission);

                        } else {
                            return $this->Forbidden('this is master permission you can not assign it to this user');

                        }

                    } else {
                        $user->givePermissionTo($permission);

                    }
                    return $this->returnSuccessMessage('the permission has been assigned successfully');
                } else {
                    return $this->returnError('this permission does not exist');}}}}


        catch
            (\Exception $exception) {
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


            $user = User::where("uuid",$request->user_uuid)->first();
            if(!$user){
                return $this->NotFound('User not found');
            }
            else {
                if($user->hasRole('Master')){
                    if($request->roleName == 'Master'){
                        return $this->Forbidden('this is master role  you can not remove it from Master');
                    }
                }

else{
            // Check if the user has the role before removing it
            if ($user->hasRole($request->roleName)) {
                // Remove the role from the user
                $user->removeRole($request->roleName);

                return $this->returnSuccessMessage('the role has been removed successfully');
            } else {
                return $this->returnError("The user doesn't have this role");
            }}}
        } catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemovePermissionsFromRole(Request $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'permissions' => 'required',
            'roleName' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }


        try {
            $role = Role::findByName($request->roleName);
            if(!$role){
                return $this->NotFound('Role not found');
            }
            else{



                foreach ($request->permissions as $permission) {
                    if ($role->hasPermissionTo($permission)) {
                        $role->revokePermissionTo($permission);

                        DB::commit();
                        return $this->returnData('role', RolesResource::make($role));
                    } else return $this->returnError("The role doesn't have this permission");
                }
                return $this->returnData('role', RolesResource::make($role));
                DB::commit();
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function RemoveDirectPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required',
            'user_uuid' => 'required|exists:users,uuid'


        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {
            DB::beginTransaction();

            $user = User::where("uuid",$request->user_uuid)->first();
            if(!$user){
                return $this->NotFound('User not found');
            }
            else{

            //            if (is_array($request->permission)) {


            foreach ($request->permission as $permission) {
                // Check if the user has the permission directly (not inherited from roles)


                if ($user->hasDirectPermission($permission)) {
                    // Remove the permission from the user
                    $user->revokePermissionTo($permission);
                } else {
                    DB::rollBack();
                    return $this->returnError(" you can not remove " . $permission . " permission its an role's permission");
                }
            }
            DB::commit();
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


        }} catch (\Exception $exception) {
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
            $user = User::where("uuid",$request->user_uuid)->first();
              if(!$user){
                  return $this->NotFound('User not found');
              }
              else{
            $permission['directed'] = $user->getDirectPermissions();
            $permission['roll'] = $user->getPermissionsViaRoles();
            return $this->returnData('permission', [
                'directed' => PermissionsResource::collection($permission['directed']),
                'roll' => PermissionsResource::collection($permission['roll'])
            ]);
        }} catch (\Exception $exception) {
            return $this->returnError($exception->getMessage());
        }
    }

    function SyncPermission(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'permission' => 'required|array|min:1',
                'roleName' => 'required|string|exists:roles,name',
                'newName' => 'required|string|unique:roles,name',
                'description' => 'required|string'
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            DB::beginTransaction();
               if($request->roleName == "Master"||$request->newName == "Master"){
                   return $this->returnError('you cannot update  Master');
               }



            $role = Role::FindByName($request->roleName);
               if(!$role){
                   return $this->NotFound('Role not found');
               }
else{

            $role->update(['name' => $request->newName, 'description' => $request->decription]);

            $permissions = exploder($request->permission);
            foreach ($permissions as $permission) {
                $singlepermission = Permission::where('name', $permission)->first();
                if(!$singlepermission){
                    return $this->NotFound('Permission not found');
                }
                if($permission->is_admin==true){

                    return $this->returnError('this is master role permission you can not assign it to this role');
                    }

                else{
                    $role->givePermissionTo($permission);
                }
            $role = $role->syncPermissions($request->permission);
            Db::commit();
            return $this->returnData('permission', RolesResource::make($role));}}
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }

    public function GetAllPermissions()
    {
        try {
            if(auth()->user()->hasrole('Master')){
                $permission = Permission::all();
            }
            else{

                $permission = Permission::where('is_admin',false)->get();
            }

            $resource = new NewPermissionsResource($permission);

            return $this->returnData('permission',$resource);
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
            return $this->returnValidationError($validator);
        }
        try {
            if($request->roleName == "Master"){
                return $this->returnError('you cannot delete  Master');
            }
            else{
                DB::beginTransaction();
                $role = Role::findByName($request->roleName);
                if(!$role){
                    return $this->NotFound('Role not found');
                }
                else{
                $role->delete();
                    DB::commit();
                return $this->returnSuccessMessage('the role deleted successfully');
            }}

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->returnError($exception->getMessage());
        }
    }
}
